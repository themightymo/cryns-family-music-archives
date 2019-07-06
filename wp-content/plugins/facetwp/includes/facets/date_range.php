<?php

class FacetWP_Facet_Date_Range extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Date Range', 'fwp' );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $value = $params['selected_values'];
        $value = empty( $value ) ? [ '', '' ] : $value;
        $fields = empty( $params['facet']['fields'] ) ? 'both' : $params['facet']['fields'];

        if ( 'exact' == $fields ) {
            $output .= '<input type="text" class="facetwp-date facetwp-date-min" value="' . esc_attr( $value[0] ) . '" placeholder="' . __( 'Date', 'fwp-front' ) . '" />';
        }
        if ( 'both' == $fields || 'start_date' == $fields ) {
            $output .= '<input type="text" class="facetwp-date facetwp-date-min" value="' . esc_attr( $value[0] ) . '" placeholder="' . __( 'Start Date', 'fwp-front' ) . '" />';
        }
        if ( 'both' == $fields || 'end_date' == $fields ) {
            $output .= '<input type="text" class="facetwp-date facetwp-date-max" value="' . esc_attr( $value[1] ) . '" placeholder="' . __( 'End Date', 'fwp-front' ) . '" />';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $values = $params['selected_values'];
        $where = '';

        $min = empty( $values[0] ) ? false : $values[0];
        $max = empty( $values[1] ) ? false : $values[1];

        $fields = isset( $facet['fields'] ) ? $facet['fields'] : 'both';
        $compare_type = empty( $facet['compare_type'] ) ? 'basic' : $facet['compare_type'];
        $is_dual = ! empty( $facet['source_other'] );

        if ( $is_dual && 'basic' != $compare_type ) {
            if ( 'exact' == $fields ) {
                $max = $min;
            }

            $min = ( false !== $min ) ? $min : '0000-00-00';
            $max = ( false !== $max ) ? $max : '3000-12-31';

            /**
             * Enclose compare
             * The post's range must surround the user-defined range
             */
            if ( 'enclose' == $compare_type ) {
                $where .= " AND LEFT(facet_value, 10) <= '$min'";
                $where .= " AND LEFT(facet_display_value, 10) >= '$max'";
            }

            /**
             * Intersect compare
             * @link http://stackoverflow.com/a/325964
             */
            if ( 'intersect' == $compare_type ) {
                $where .= " AND LEFT(facet_value, 10) <= '$max'";
                $where .= " AND LEFT(facet_display_value, 10) >= '$min'";
            }
        }

        /**
         * Basic compare
         * The user-defined range must surround the post's range
         */
        else {
            if ( 'exact' == $fields ) {
                $max = $min;
            }
            if ( false !== $min ) {
                $where .= " AND LEFT(facet_value, 10) >= '$min'";
            }
            if ( false !== $max ) {
                $where .= " AND LEFT(facet_display_value, 10) <= '$max'";
            }
        }

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' $where";
        return facetwp_sql( $sql, $facet );
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        $locale = get_locale();
        $locale = empty( $locale ) ? 'en' : substr( $locale, 0, 2 );
        $locale = ( 'ca' == $locale ) ? 'cat' : $locale;

        FWP()->display->json['datepicker'] = [
            'locale'    => $locale,
            'clearText' => __( 'Clear', 'fwp-front' ),
            'fromText'  => __( 'from', 'fwp-front' ),
            'toText'    => __( 'to', 'fwp-front' )
        ];
        FWP()->display->assets['flatpickr.css'] = FACETWP_URL . '/assets/vendor/flatpickr/flatpickr.css';
        FWP()->display->assets['flatpickr.js'] = FACETWP_URL . '/assets/vendor/flatpickr/flatpickr.min.js';

        if ( 'en' != $locale ) {
            FWP()->display->assets['flatpickr-l10n.js'] = FACETWP_URL . "/assets/vendor/flatpickr/l10n/$locale.js";
        }
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <div class="facetwp-row">
            <div>
                <?php _e('Other data source', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Use a separate value for the upper limit?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <data-sources
                    :facet="facet"
                    :selected="facet.source_other"
                    :sources="$root.data_sources"
                    settingName="source_other">
                </data-sources>
            </div>
        </div>
        <div class="facetwp-row">
            <div><?php _e('Compare type', 'fwp'); ?>:</div>
            <div>
                <select class="facet-compare-type">
                    <option value=""><?php _e( 'Basic', 'fwp' ); ?></option>
                    <option value="enclose"><?php _e( 'Enclose', 'fwp' ); ?></option>
                    <option value="intersect"><?php _e( 'Intersect', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row">
            <div><?php _e('Fields to show', 'fwp'); ?>:</div>
            <div>
                <select class="facet-fields">
                    <option value="both"><?php _e( 'Start + End Dates', 'fwp' ); ?></option>
                    <option value="exact"><?php _e( 'Exact Date', 'fwp' ); ?></option>
                    <option value="start_date"><?php _e( 'Start Date', 'fwp' ); ?></option>
                    <option value="end_date"><?php _e( 'End Date', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e('Display format', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">See available <a href="https://chmln.github.io/flatpickr/formatting/" target="_blank">formatting tokens</a></div>
                </div>
            </div>
            <div><input type="text" class="facet-format" placeholder="Y-m-d" /></div>
        </div>
<?php
    }


    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        $format = empty( $params['facet']['format'] ) ? 'Y-m-d' : $params['facet']['format'];
        return [ 'format' => $format ];
    }
}
