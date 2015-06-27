<?php

class FacetWP_Ajax
{

    function __construct() {
        // ajax settings
        add_action( 'wp_ajax_facetwp_load', array( $this, 'load_settings' ) );
        add_action( 'wp_ajax_facetwp_save', array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_facetwp_refresh', array( $this, 'refresh' ) );
        add_action( 'wp_ajax_nopriv_facetwp_refresh', array( $this, 'refresh' ) );
        add_action( 'wp_ajax_nopriv_facetwp_resume_index', array( $this, 'resume_index' ) );
        add_action( 'wp_ajax_facetwp_rebuild_index', array( $this, 'rebuild_index' ) );
        add_action( 'wp_ajax_facetwp_heartbeat', array( $this, 'heartbeat' ) );
        add_action( 'wp_ajax_facetwp_license', array( $this, 'license' ) );
        add_action( 'wp_ajax_facetwp_migrate', array( $this, 'migrate' ) );

        // handle requests without templates
        $action = isset( $_POST['action'] ) ? $_POST['action'] : '';
        if ( 'facetwp_refresh' == $action && 'wp' == $_POST['data']['template'] ) {
            ob_start();

            add_action( 'pre_get_posts', array( $this, 'update_query_vars' ), 999 );
            add_action( 'shutdown', array( $this, 'inject_template' ), 0 );
        }
    }


    /**
     * Force FacetWP to use the default WP query
     */
    function update_query_vars( $query ) {

        // Only run once
        if ( isset( $this->query_vars ) ) {
            return;
        }

        $is_main_query = ( $query->is_archive || $query->is_search || ( $query->is_main_query() && ! $query->is_singular ) );
        $is_main_query = apply_filters( 'facetwp_is_main_query', $is_main_query, $query );

        if ( $is_main_query ) {

            // Store the default WP query vars
            $this->query_vars = $query->query_vars;

            // Tell FacetWP to use the default WP template
            $params = $this->process_post_data();
            $params['template'] = 'wp';

            // Generate the facet output
            $this->output = FWP()->facet->render( $params );

            // Set up the updated query_vars
            $query->query_vars = FWP()->facet->query_args;
        }
    }


    /**
     * Inject the page HTML into the JSON response
     * We'll cherry-pick the content from the HTML using front.js
     */
    function inject_template() {
        $this->output['template'] = ob_get_clean();
        echo json_encode( $this->output );
        exit;
    }



    /**
     * Load admin settings
     */
    function load_settings() {
        if ( current_user_can( 'manage_options' ) ) {
            echo json_encode( FWP()->helper->settings_raw );
        }
        exit;
    }


    /**
     * Save admin settings
     */
    function save_settings() {
        if ( current_user_can( 'manage_options' ) ) {
            $settings = stripslashes( $_POST['data'] );
            update_option( 'facetwp_settings', $settings );
            echo __( 'Settings saved', 'fwp' );
        }
        exit;
    }


    /**
     * Rebuild the index table
     */
    function rebuild_index() {
        if ( current_user_can( 'manage_options' ) ) {
            FWP()->indexer->index();
        }
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


    /**
     * Generate a $params array that can be passed directly into FWP()->facet->render()
     */
    function process_post_data() {
        $data = stripslashes_deep( $_POST['data'] );
        $facets = json_decode( $data['facets'] );
        $extras = isset( $data['extras'] ) ? $data['extras'] : array();

        $params = array(
            'facets'            => array(),
            'template'          => $data['template'],
            'static_facet'      => $data['static_facet'],
            'http_params'       => $data['http_params'],
            'extras'            => $extras,
            'soft_refresh'      => (int) $data['soft_refresh'],
            'paged'             => (int) $data['paged'],
        );

        foreach ( $facets as $facet_name => $selected_values ) {
            $params['facets'][] = array(
                'facet_name'        => $facet_name,
                'selected_values'   => $selected_values,
            );
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

        // Query debugging
        if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
            $queries = array();
            foreach ( $wpdb->queries as $query ) {
                $sql = preg_replace( "/[\s]/", ' ', $query[0] );
                $sql = preg_replace( "/[ ]{2,}/", ' ', $sql );

                $queries[] = array(
                    'sql'   => $sql,
                    'time'  => $query[1],
                    'stack' => $query[2],
                );
            }
            $output['queries'] = $queries;
        }

        $output = json_encode( $output );

        echo apply_filters( 'facetwp_ajax_response', $output, array(
            'data' => $data
        ) );

        exit;
    }


    /**
     * Keep track of indexing progress
     */
    function heartbeat() {
        echo FWP()->indexer->get_progress();
        exit;
    }


    /**
     * Import / export functionality
     */
    function migrate() {
        $action_type = $_POST['action_type'];

        $output = array();

        if ( 'export' == $action_type ) {
            $items = $_POST['items'];

            if ( !empty( $items ) ) {
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

            $status = array(
                'imported' => array(),
                'skipped' => array(),
            );

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

        $request = wp_remote_post( 'https://facetwp.com/updater/', array(
            'body' => array(
                'action'        => 'activate',
                'slug'          => 'facetwp',
                'license'       => $license,
                'host'          => FWP()->helper->get_http_host(),
            )
        ) );

        if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {
            update_option( 'facetwp_license', $license );
            update_option( 'facetwp_activation', $request['body'] );
            echo $request['body'];
        }
        else {
            echo json_encode( array(
                'status'    => 'error',
                'message'   => __( 'Unable to connect to activation server', 'fwp' ),
            ) );
        }
        exit;
    }
}
