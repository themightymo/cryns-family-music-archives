<?php

class FacetWP_Facet_Date_Range
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
        $value = empty( $value ) ? array( '', '' ) : $value;
        $fields = empty( $params['facet']['fields'] ) ? 'both' : $params['facet']['fields'];

        if ( 'both' == $fields || 'start_date' == $fields ) {
            $output .= '<label>' . __( 'Start Date', 'fwp' ) . '</label>';
            $output .= '<input type="text" class="facetwp-date facetwp-date-min" value="' . $value[0] . '" />';
        }
        if ( 'both' == $fields || 'end_date' == $fields ) {
            $output .= '<label>' . __( 'End Date', 'fwp' ) . '</label>';
            $output .= '<input type="text" class="facetwp-date facetwp-date-max" value="' . $value[1] . '" />';
        }
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $dates = $params['selected_values'];
        $where = '';

        if ( '' != $dates[0] ) {
            $length = strlen( $dates[0] );
            $where .= " AND LEFT(facet_value, $length) >= '" . $dates[0] . "'";
        }
        if ( '' != $dates[1] ) {
            $length = strlen( $dates[1] );
            $where .= " AND LEFT(facet_value, $length) <= '" . $dates[1] . "'";
        }
        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' $where";
        return $wpdb->get_col( $sql );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/date_range', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.facet-fields').val(obj.fields);
    });

    wp.hooks.addFilter('facetwp/save/date_range', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['fields'] = $this.find('.facet-fields').val();
        return obj;
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
?>
<link href="<?php echo FACETWP_URL; ?>/assets/js/bootstrap-datepicker/datepicker.css" rel="stylesheet">
<script src="<?php echo FACETWP_URL; ?>/assets/js/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script>
(function($) {
    wp.hooks.addAction('facetwp/refresh/date_range', function($this, facet_name) {
        var min = $this.find('.facetwp-date-min').val() || '';
        var max = $this.find('.facetwp-date-max').val() || '';
        FWP.facets[facet_name] = ('' != min || '' != max) ? [min, max] : [];
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('facetwp-loaded', function() {
            $('.facetwp-date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                clearBtn: true
            }).on('changeDate', function(e) {
                FWP.autoload();
            });
        });
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <tr class="facetwp-conditional type-date_range">
            <td><?php _e('Fields to show', 'fwp'); ?>:</td>
            <td>
                <select class="facet-fields">
                    <option value="both"><?php _e( 'Both', 'fwp' ); ?></option>
                    <option value="start_date"><?php _e( 'Start Date', 'fwp' ); ?></option>
                    <option value="end_date"><?php _e( 'End Date', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
<?php
    }
}
