<?php

class FacetWP_Facet_Number_Range extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Number Range', 'fwp' );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $value = $params['selected_values'];
        $value = empty( $value ) ? [ '', '', ] : $value;
        $fields = empty( $params['facet']['fields'] ) ? 'both' : $params['facet']['fields'];

        if ( 'exact' == $fields ) {
            $output .= '<input type="text" class="facetwp-number facetwp-number-min" value="' . esc_attr( $value[0] ) . '" placeholder="' . __( 'Number', 'fwp-front' ) . '" />';
        }
        if ( 'both' == $fields || 'min' == $fields ) {
            $output .= '<input type="text" class="facetwp-number facetwp-number-min" value="' . esc_attr( $value[0] ) . '" placeholder="' . __( 'Min', 'fwp-front' ) . '" />';
        }
        if ( 'both' == $fields || 'max' == $fields ) {
            $output .= '<input type="text" class="facetwp-number facetwp-number-max" value="' . esc_attr( $value[1] ) . '" placeholder="' . __( 'Max', 'fwp-front' ) . '" />';
        }

        $output .= '<input type="button" class="facetwp-submit" value="' . __( 'Go', 'fwp-front' ) . '" />';

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

        $min = ( '' == $values[0] ) ? false : FWP()->helper->format_number( $values[0] );
        $max = ( '' == $values[1] ) ? false : FWP()->helper->format_number( $values[1] );

        $fields = isset( $facet['fields'] ) ? $facet['fields'] : 'both';
        $compare_type = empty( $facet['compare_type'] ) ? 'basic' : $facet['compare_type'];
        $is_dual = ! empty( $facet['source_other'] );

        if ( $is_dual && 'basic' != $compare_type ) {
            if ( 'exact' == $fields ) {
                $max = $min;
            }

            $min = ( false !== $min ) ? $min : -999999999999;
            $max = ( false !== $max ) ? $max : 999999999999;

            /**
             * Enclose compare
             * The post's range must surround the user-defined range
             */
            if ( 'enclose' == $compare_type ) {
                $where .= " AND (facet_value + 0) <= '$min'";
                $where .= " AND (facet_display_value + 0) >= '$max'";
            }

            /**
             * Intersect compare
             * @link http://stackoverflow.com/a/325964
             */
            if ( 'intersect' == $compare_type ) {
                $where .= " AND (facet_value + 0) <= '$max'";
                $where .= " AND (facet_display_value + 0) >= '$min'";
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
                $where .= " AND (facet_value + 0) >= '$min'";
            }
            if ( false !== $max ) {
                $where .= " AND (facet_display_value + 0) <= '$max'";
            }
        }

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' $where";
        return facetwp_sql( $sql, $facet );
    }


    /**
     * (Admin) Output settings HTML
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
            <div><?php _e('Fields to show', 'fwp'); ?>:</div>
            <div>
                <select class="facet-fields">
                    <option value="both"><?php _e( 'Min + Max', 'fwp' ); ?></option>
                    <option value="exact"><?php _e( 'Exact', 'fwp' ); ?></option>
                    <option value="min"><?php _e( 'Min', 'fwp' ); ?></option>
                    <option value="max"><?php _e( 'Max', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.source_other">
            <div><?php _e('Compare type', 'fwp'); ?>:</div>
            <div>
                <select class="facet-compare-type">
                    <option value=""><?php _e( 'Basic', 'fwp' ); ?></option>
                    <option value="enclose"><?php _e( 'Enclose', 'fwp' ); ?></option>
                    <option value="intersect"><?php _e( 'Intersect', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
<?php
    }
}
