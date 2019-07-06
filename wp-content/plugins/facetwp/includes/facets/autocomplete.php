<?php

class FacetWP_Facet_Autocomplete extends FacetWP_Facet
{

    public $is_buffering = false;


    function __construct() {
        $this->label = __( 'Autocomplete', 'fwp' );

        // ajax
        add_action( 'facetwp_autocomplete_load', [ $this, 'ajax_load' ] );

        // css-based template
        $this->maybe_buffer_output();
        add_action( 'facetwp_found_main_query', [ $this, 'template_handler' ] );

        // deprecated
        add_action( 'wp_ajax_facetwp_autocomplete_load', [ $this, 'ajax_load' ] );
        add_action( 'wp_ajax_nopriv_facetwp_autocomplete_load', [ $this, 'ajax_load' ] );
    }


    /**
     * For page templates with a custom WP_Query, we need to prevent the
     * page header from being output with the autocomplete JSON
     */
    function maybe_buffer_output() {
        if ( isset( $_POST['action'] ) && 'facetwp_autocomplete_load' == $_POST['action'] ) {
            $this->is_buffering = true;
            ob_start();
        }
    }


    /**
     * For CSS-based templates, the "facetwp_autocomplete_load" action isn't fired
     * so we need to manually check the action
     */
    function template_handler() {
        if ( isset( $_POST['action'] ) && 'facetwp_autocomplete_load' == $_POST['action'] ) {
            if ( $this->is_buffering ) {
                while ( ob_get_level() ) {
                    ob_end_clean();
                }
            }
            $this->ajax_load();
        }
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $value = (array) $params['selected_values'];
        $value = empty( $value ) ? '' : stripslashes( $value[0] );
        $placeholder = isset( $params['facet']['placeholder'] ) ? $params['facet']['placeholder'] : __( 'Start typing', 'fwp-front' ) + '...';
        $placeholder = facetwp_i18n( $placeholder );
        $output .= '<input type="search" class="facetwp-autocomplete" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '" />';
        $output .= '<input type="button" class="facetwp-autocomplete-update" value="' . __( 'Go', 'fwp-front' ) . '" />';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;
        $selected_values = stripslashes( $selected_values );

        if ( empty( $selected_values ) ) {
            return 'continue';
        }

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = %s AND facet_display_value LIKE %s";

        $sql = $wpdb->prepare( $sql, $facet['name'], '%' . $selected_values . '%' );
        return facetwp_sql( $sql, $facet );
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->json['no_results'] = __( 'No results', 'fwp-front' );
        FWP()->display->assets['jquery.autocomplete.js'] = FACETWP_URL . '/assets/vendor/jquery-autocomplete/jquery.autocomplete.min.js';
        FWP()->display->assets['jquery.autocomplete.css'] = FACETWP_URL . '/assets/vendor/jquery-autocomplete/jquery.autocomplete.css';
    }


    /**
     * Load facet values via AJAX
     */
    function ajax_load() {
        global $wpdb;

        // optimizations
        $_POST['data']['soft_refresh'] = 1;
        $_POST['data']['extras'] = [];

        $query = stripslashes( $_POST['query'] );
        $query = FWP()->helper->sanitize( $wpdb->esc_like( $query ) );
        $facet_name = FWP()->helper->sanitize( $_POST['facet_name'] );
        $output = [];

        // simulate a refresh
        FWP()->facet->render(
            FWP()->ajax->process_post_data()
        );

        // then grab the matching post IDs
        $where_clause = $this->get_where_clause( [ 'name' => $facet_name ] );

        if ( ! empty( $query ) && ! empty( $facet_name ) ) {
            $sql = "
            SELECT DISTINCT facet_display_value
            FROM {$wpdb->prefix}facetwp_index
            WHERE
                facet_name = '$facet_name' AND
                facet_display_value LIKE '%$query%'
                $where_clause
            ORDER BY facet_display_value ASC
            LIMIT 10";

            $results = $wpdb->get_results( $sql );

            foreach ( $results as $result ) {
                $output[] = [
                    'value' => $result->facet_display_value,
                    'data' => $result->facet_display_value,
                ];
            }
        }

        wp_send_json( [ 'suggestions' => $output ] );
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <div class="facetwp-row">
            <div><?php _e( 'Placeholder text', 'fwp' ); ?>:</div>
            <div><input type="text" class="facet-placeholder" /></div>
        </div>
<?php
    }
}
