<?php

class FacetWP_Facet_Number_Range
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
        $value = empty( $value ) ? array( '', '', ) : $value;
        $output .= '<label>' . __( 'Min', 'fwp' ) . '</label>';
        $output .= '<input type="text" class="facetwp-number facetwp-number-min" value="' . $value[0] . '" />';
        $output .= '<label>' . __( 'Max', 'fwp' ) . '</label>';
        $output .= '<input type="text" class="facetwp-number facetwp-number-max" value="' . $value[1] . '" />';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $numbers = $params['selected_values'];
        $where = '';

        if ( '' != $numbers[0] ) {
            $where .= " AND (facet_value + 0) >= '" . $numbers[0] . "'";
        }
        if ( '' != $numbers[1] ) {
            $where .= " AND (facet_value + 0) <= '" . $numbers[1] . "'";
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
    wp.hooks.addAction('facetwp/load/number_range', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
    });

    wp.hooks.addFilter('facetwp/save/number_range', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
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
<script>
(function($) {
    wp.hooks.addAction('facetwp/refresh/number_range', function($this, facet_name) {
        var min = $this.find('.facetwp-number-min').val() || '';
        var max = $this.find('.facetwp-number-max').val() || '';
        FWP.facets[facet_name] = ('' != min || '' != max) ? [min, max] : [];
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('blur', '.facetwp-number-min, .facetwp-number-max', function() {
            FWP.autoload();
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

    }
}
