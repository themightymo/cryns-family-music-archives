<?php

class FacetWP_Ajax
{

    /* (array) FacetWP-related GET variables */
    public $url_vars = [];

    /* (boolean) FWP template shortcode? */
    public $is_shortcode = false;

    /* (boolean) Is a FacetWP refresh? */
    public $is_refresh = false;

    /* (boolean) Initial load? */
    public $is_preload;


    function __construct() {

        // Authenticated
        if ( current_user_can( 'manage_options' ) ) {
            if ( check_ajax_referer( 'fwp_admin_nonce', 'nonce', false ) ) {
                add_action( 'wp_ajax_facetwp_save', [ $this, 'save_settings' ] );
                add_action( 'wp_ajax_facetwp_rebuild_index', [ $this, 'rebuild_index' ] );
                add_action( 'wp_ajax_facetwp_get_info', [ $this, 'get_info' ] );
                add_action( 'wp_ajax_facetwp_get_query_args', [ $this, 'get_query_args' ] );
                add_action( 'wp_ajax_facetwp_heartbeat', [ $this, 'heartbeat' ] );
                add_action( 'wp_ajax_facetwp_license', [ $this, 'license' ] );
                add_action( 'wp_ajax_facetwp_backup', [ $this, 'backup' ] );
            }
        }

        // Non-authenticated
        add_action( 'facetwp_refresh', [ $this, 'refresh' ] );
        add_action( 'wp_ajax_nopriv_facetwp_resume_index', [ $this, 'resume_index' ] );

        // Deprecated
        add_action( 'wp_ajax_facetwp_refresh', [ $this, 'refresh' ] );
        add_action( 'wp_ajax_nopriv_facetwp_refresh', [ $this, 'refresh' ] );

        // Intercept the template if needed
        $this->intercept_request();
    }


    /**
     * If AJAX and the template is "wp", return the buffered HTML
     * Otherwise, store the GET variables for later use
     */
    function intercept_request() {
        $action = isset( $_POST['action'] ) ? $_POST['action'] : '';

        $valid_actions = [
            'facetwp_refresh',
            'facetwp_autocomplete_load'
        ];

        $this->is_refresh = ( 'facetwp_refresh' == $action );
        $this->is_preload = ! in_array( $action, $valid_actions );
        $prefix = FWP()->helper->get_setting( 'prefix' );
        $tpl = isset( $_POST['data']['template'] ) ? $_POST['data']['template'] : '';

        // Pageload
        if ( $this->is_preload ) {

            // Store GET variables
            foreach ( $_GET as $key => $val ) {
                if ( 0 === strpos( $key, $prefix ) ) {
                    $new_key = substr( $key, strlen( $prefix ) );
                    $new_val = stripslashes( $val );

                    if ( '' !== $new_val ) {
                        if ( ! in_array( $new_key, [ 'paged', 'per_page', 'sort' ] ) ) {
                            $new_val = explode( ',', $new_val );
                        }

                        $this->url_vars[ $new_key ] = $new_val;
                    }
                }
            }

            $this->url_vars = apply_filters( 'facetwp_preload_url_vars', $this->url_vars );
        }

        if ( $this->is_preload || 'wp' == $tpl ) {
            add_action( 'pre_get_posts', [ $this, 'sacrificial_lamb' ], 998 );
            add_action( 'pre_get_posts', [ $this, 'update_query_vars' ], 999 );
        }

        if ( ! $this->is_preload && 'wp' == $tpl && 'facetwp_autocomplete_load' != $action ) {
            add_action( 'shutdown', [ $this, 'inject_template' ], 0 );
            ob_start();
        }
    }


    function sacrificial_lamb( $query ) {
        // Fix for WP core issue #40393
    }


    /**
     * Force FacetWP to use the default WP query
     */
    function update_query_vars( $query ) {

        // Only run once
        if ( isset( $this->query_vars ) ) {
            return;
        }

        // Skip shortcode template
        if ( $this->is_shortcode ) {
            return;
        }

        // Skip admin
        if ( is_admin() && ! wp_doing_ajax() ) {
            return;
        }

        $is_main_query = ( $query->is_archive || $query->is_search || ( $query->is_main_query() && ! $query->is_singular ) );
        $is_main_query = ( true === $query->get( 'suppress_filters', false ) ) ? false : $is_main_query; // skip get_posts()
        $is_main_query = ( wp_doing_ajax() && ! $this->is_refresh ) ? false : $is_main_query; // skip other ajax
        $is_main_query = ( $query->is_feed ) ? false : $is_main_query; // skip feeds
        $is_main_query = ( '' !== $query->get( 'facetwp' ) ) ? (bool) $query->get( 'facetwp' ) : $is_main_query; // flag
        $is_main_query = apply_filters( 'facetwp_is_main_query', $is_main_query, $query );

        if ( $is_main_query ) {

            // Set the flag
            $query->set( 'facetwp', true );

            // Store the default WP query vars
            $this->query_vars = $query->query_vars;

            // Notify
            do_action( 'facetwp_found_main_query' );

            // No URL variables
            if ( $this->is_preload && empty( $this->url_vars ) ) {
                return;
            }

            // Generate the FWP output
            if ( $this->is_preload ) {
                $this->get_preload_data( 'wp' );
            }
            else {
                $this->output = FWP()->facet->render(
                    $this->process_post_data()
                );
            }

            // Set up the updated query_vars
            $query->query_vars = FWP()->facet->query_args;
        }
    }


    /**
     * Preload the AJAX response so search engines can see it
     * @since 2.0
     */
    function get_preload_data( $template_name, $overrides = [] ) {

        if ( false === $template_name ) {
            $template_name = isset( $this->template_name ) ? $this->template_name : 'wp';
        }

        $this->template_name = $template_name;

        // Is this a template shortcode?
        $this->is_shortcode = ( 'wp' != $template_name );

        $params = [
            'facets'            => [],
            'template'          => $template_name,
            'http_params'       => [
                'get'       => $_GET,
                'uri'       => FWP()->helper->get_uri(),
                'url_vars'  => FWP()->ajax->url_vars,
            ],
            'frozen_facets'     => [],
            'soft_refresh'      => 0,
            'is_preload'        => 1,
            'is_bfcache'        => 0,
            'first_load'        => 0, // force load template
            'extras'            => [],
            'paged'             => 1,
        ];

        foreach ( $this->url_vars as $key => $val ) {
            if ( 'paged' == $key ) {
                $params['paged'] = $val;
            }
            elseif ( 'per_page' == $key ) {
                $params['extras']['per_page'] = $val;
            }
            elseif ( 'sort' == $key ) {
                $params['extras']['sort'] = $val;
            }
            else {
                $params['facets'][] = [
                    'facet_name' => $key,
                    'selected_values' => $val,
                ];
            }
        }

        // Override the defaults
        $params = array_merge( $params, $overrides );

        return FWP()->facet->render( $params );
    }


    /**
     * Inject the page HTML into the JSON response
     * We'll cherry-pick the content from the HTML using front.js
     */
    function inject_template() {
        $html = ob_get_clean();

        // Throw an error
        if ( empty( $this->output['settings'] ) ) {
            $html = __( 'FacetWP was unable to auto-detect the post listing', 'fwp' );
        }
        // Grab the <body> contents
        else {
            preg_match( "/<body(.*?)>(.*?)<\/body>/s", $html, $matches );

            if ( ! empty( $matches ) ) {
                $html = trim( $matches[2] );
            }
        }

        $this->output['template'] = $html;
        do_action( 'facetwp_inject_template', $this->output );
        wp_send_json( $this->output );
    }


    /**
     * Save admin settings
     */
    function save_settings() {
        $settings = stripslashes( $_POST['data'] );
        $json_test = json_decode( $settings, true );

        // Check for valid JSON
        if ( isset( $json_test['settings'] ) ) {
            update_option( 'facetwp_settings', $settings );
            $response = [
                'code' => 'success',
                'message' => __( 'Settings saved', 'fwp' ),
                'reindex' => FWP()->diff->is_reindex_needed()
            ];
        }
        else {
            $response = [
                'code' => 'error',
                'message' => __( 'Error: invalid JSON', 'fwp' )
            ];
        }

        wp_send_json( $response );
    }


    /**
     * Rebuild the index table
     */
    function rebuild_index() {
        FWP()->indexer->index();
        exit;
    }


    /**
     * Resume stalled indexer
     */
    function resume_index() {
        $touch = (int) FWP()->indexer->get_transient( 'touch' );
        if ( 0 < $touch && $_POST['touch'] == $touch ) {
            FWP()->indexer->index();
        }
        exit;
    }


    function get_info() {
        $type = $_POST['type'];

        if ( 'post_types' == $type ) {
            $post_types = get_post_types( [ 'exclude_from_search' => false, '_builtin' => false ] );
            $post_types = [ 'post', 'page' ] + $post_types;
            sort( $post_types );

            $response = [
                'code' => 'success',
                'message' => implode( ', ', $post_types )
            ];
        }
        elseif ( 'indexer_stats' == $type ) {
            global $wpdb;

            $row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}facetwp_index" );
            $facet_count = $wpdb->get_var( "SELECT COUNT(DISTINCT facet_name) FROM {$wpdb->prefix}facetwp_index" );
            $last_indexed = get_option( 'facetwp_last_indexed' );
            $last_indexed = $last_indexed ? human_time_diff( $last_indexed ) . ' ago' : 'N/A';

            $response = [
                'code' => 'success',
                'message' => "rows: $row_count, facets: $facet_count, last re-index: $last_indexed"
            ];
        }
        elseif ( 'cancel_reindex' == $type ) {
            update_option( 'facetwp_indexing', '' );

            $response = [
                'code' => 'success',
                'message' => 'Indexing cancelled'
            ];
        }
        elseif ( 'purge_index_table' == $type ) {
            global $wpdb;

            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}facetwp_index" );
            delete_option( 'facetwp_version' );

            $response = [
                'code' => 'success',
                'message' => __( 'Done, please re-index', 'fwp' )
            ];
        }

        wp_send_json( $response );
    }


    /**
     * Return query arguments based on a Query Builder object
     */
    function get_query_args() {
        $query_obj = $_POST['query_obj'];

        if ( is_array( $query_obj ) ) {
            $query_args = FWP()->builder->parse_query_obj( $query_obj );
        }

        wp_send_json( $query_args );
    }


    /**
     * Generate a $params array that can be passed directly into FWP()->facet->render()
     */
    function process_post_data() {
        $data = stripslashes_deep( $_POST['data'] );
        $facets = json_decode( $data['facets'], true );
        $extras = isset( $data['extras'] ) ? $data['extras'] : [];
        $frozen_facets = isset( $data['frozen_facets'] ) ? $data['frozen_facets'] : [];

        $params = [
            'facets'            => [],
            'template'          => $data['template'],
            'frozen_facets'     => $frozen_facets,
            'http_params'       => $data['http_params'],
            'extras'            => $extras,
            'soft_refresh'      => (int) $data['soft_refresh'],
            'is_bfcache'        => (int) $data['is_bfcache'],
            'first_load'        => (int) $data['first_load'],
            'paged'             => (int) $data['paged'],
        ];

        foreach ( $facets as $facet_name => $selected_values ) {
            $params['facets'][] = [
                'facet_name'        => $facet_name,
                'selected_values'   => $selected_values,
            ];
        }

        return $params;
    }


    /**
     * The AJAX facet refresh handler
     */
    function refresh() {

        global $wpdb;

        $params = $this->process_post_data();
        $output = FWP()->facet->render( $params );
        $data = stripslashes_deep( $_POST['data'] );
        $output = json_encode( $output );

        echo apply_filters( 'facetwp_ajax_response', $output, [
            'data' => $data
        ] );

        exit;
    }


    /**
     * Keep track of indexing progress
     */
    function heartbeat() {
        $output = [
            'pct' => FWP()->indexer->get_progress()
        ];

        if ( -1 == $output['pct'] ) {
            $output['rows'] = FWP()->helper->get_row_counts();
        }

        wp_send_json( $output );
    }


    /**
     * Import / export functionality
     */
    function backup() {
        $action_type = $_POST['action_type'];
        $output = [];

        if ( 'export' == $action_type ) {
            $items = $_POST['items'];

            if ( ! empty( $items ) ) {
                foreach ( $items as $item ) {
                    if ( 'facet' == substr( $item, 0, 5 ) ) {
                        $item_name = substr( $item, 6 );
                        $output['facets'][] = FWP()->helper->get_facet_by_name( $item_name );
                    }
                    elseif ( 'template' == substr( $item, 0, 8 ) ) {
                        $item_name = substr( $item, 9 );
                        $output['templates'][] = FWP()->helper->get_template_by_name( $item_name );
                    }
                }
            }
            echo json_encode( $output );
        }
        elseif ( 'import' == $action_type ) {
            $settings = FWP()->helper->settings;
            $import_code = json_decode( stripslashes( $_POST['import_code'] ), true );
            $overwrite = (int) $_POST['overwrite'];

            if ( empty( $import_code ) || ! is_array( $import_code ) ) {
                _e( 'Nothing to import', 'fwp' );
                exit;
            }

            $status = [
                'imported' => [],
                'skipped' => [],
            ];

            foreach ( $import_code as $object_type => $object_items ) {
                foreach ( $object_items as $object_item ) {
                    $is_match = false;
                    foreach ( $settings[$object_type] as $key => $settings_item ) {
                        if ( $object_item['name'] == $settings_item['name'] ) {
                            if ( $overwrite ) {
                                $settings[$object_type][$key] = $object_item;
                                $status['imported'][] = $object_item['label'];
                            }
                            else {
                                $status['skipped'][] = $object_item['label'];
                            }
                            $is_match = true;
                            break;
                        }
                    }

                    if ( ! $is_match ) {
                        $settings[$object_type][] = $object_item;
                        $status['imported'][] = $object_item['label'];
                    }
                }
            }

            update_option( 'facetwp_settings', json_encode( $settings ) );

            if ( ! empty( $status['imported'] ) ) {
                echo ' [<strong>' . __( 'Imported', 'fwp' ) . '</strong>] ' . implode( ', ', $status['imported'] );
            }
            if ( ! empty( $status['skipped'] ) ) {
                echo ' [<strong>' . __( 'Skipped', 'fwp' ) . '</strong>] ' . implode( ', ', $status['skipped'] );
            }
        }

        exit;
    }


    /**
     * License activation
     */
    function license() {
        $license = $_POST['license'];

        $request = wp_remote_post( 'http://api.facetwp.com', [
            'body' => [
                'action'        => 'activate',
                'slug'          => 'facetwp',
                'license'       => $license,
                'host'          => FWP()->helper->get_http_host(),
            ]
        ] );

        if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {
            update_option( 'facetwp_license', $license );
            update_option( 'facetwp_activation', $request['body'] );
            update_option( 'facetwp_updater_last_checked', 0 );
            echo $request['body'];
        }
        else {
            echo json_encode( [
                'status'    => 'error',
                'message'   => __( 'Error', 'fwp' ) . ': ' . $request->get_error_message(),
            ] );
        }

        exit;
    }
}
