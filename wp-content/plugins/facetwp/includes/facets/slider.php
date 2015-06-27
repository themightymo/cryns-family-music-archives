<?php

class FacetWP_Facet_Slider
{

    function __construct() {
        $this->label = __( 'Slider', 'fwp' );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $value = $params['selected_values'];
        $output .= '<div class="facetwp-slider-wrap">';
        $output .= '<div class="facetwp-slider"></div>';
        $output .= '</div>';
        $output .= '<span class="facetwp-slider-label"></span>';
        $output .= '<div><input type="button" class="facetwp-slider-reset" value="' . __( 'Reset', 'fwp' ) . '" /></div>';
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

        if ( !empty( $values[0] ) ) {
            $where .= " AND CAST(facet_display_value AS DECIMAL(10,2)) >= '{$values[0]}'";
        }
        if ( !empty( $values[1] ) ) {
            $where .= " AND CAST(facet_display_value AS DECIMAL(10,2)) <= '{$values[1]}'";
        }

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' $where";
        return $wpdb->get_col( $sql );
    }


    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $where_clause = $params['where_clause'];
        $selected_values = $params['selected_values'];

        // Set default slider values
        $defaults = array(
            'format' => '',
            'prefix' => '',
            'suffix' => '',
            'step' => 1,
        );
        $facet = array_merge( $defaults, $facet );

        $min = $wpdb->get_var( "SELECT facet_display_value FROM {$wpdb->prefix}facetwp_index WHERE facet_name = '{$facet['name']}' $where_clause ORDER BY (facet_display_value + 0) ASC LIMIT 1" );
        $max = $wpdb->get_var( "SELECT facet_display_value FROM {$wpdb->prefix}facetwp_index WHERE facet_name = '{$facet['name']}' $where_clause ORDER BY (facet_display_value + 0) DESC LIMIT 1" );

        $selected_min = isset( $selected_values[0] ) ? $selected_values[0] : $min;
        $selected_max = isset( $selected_values[1] ) ? $selected_values[1] : $max;

        return array(
            'range' => array(
                'min' => (float) $selected_min,
                'max' => (float) $selected_max
            ),
            'start' => array( $min, $max ),
            'format' => $facet['format'],
            'prefix' => $facet['prefix'],
            'suffix' => $facet['suffix'],
            'step' => $facet['step']
        );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/slider', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.type-slider .facet-prefix').val(obj.prefix);
        $this.find('.type-slider .facet-suffix').val(obj.suffix);
        $this.find('.type-slider .facet-format').val(obj.format);
        $this.find('.type-slider .facet-step').val(obj.step);
    });

    wp.hooks.addFilter('facetwp/save/slider', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['prefix'] = $this.find('.type-slider .facet-prefix').val();
        obj['suffix'] = $this.find('.type-slider .facet-suffix').val();
        obj['format'] = $this.find('.type-slider .facet-format').val();
        obj['step'] = $this.find('.type-slider .facet-step').val();
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
<link href="<?php echo FACETWP_URL; ?>/assets/js/noUiSlider/jquery.nouislider.css" rel="stylesheet">
<script src="<?php echo FACETWP_URL; ?>/assets/js/noUiSlider/jquery.nouislider.min.js"></script>
<script src="<?php echo FACETWP_URL; ?>/assets/js/nummy/nummy.min.js"></script>
<script>


FWP.used_facets = {};


(function($) {
    wp.hooks.addAction('facetwp/refresh/slider', function($this, facet_name) {
        FWP.facets[facet_name] = [];
        // The settings have already been loaded
        if ('undefined' !== typeof FWP.used_facets[facet_name]) {
            FWP.facets[facet_name] = $this.find('.facetwp-slider').val();
        }
    });

    wp.hooks.addAction('facetwp/set_label/slider', function($this) {
        var facet_name = $this.attr('data-name');
        var min = FWP.settings[facet_name]['lower'];
        var max = FWP.settings[facet_name]['upper'];
        var format = FWP.settings[facet_name]['format'];
        if ( min === max ) {
            var label = FWP.settings[facet_name]['prefix']
                + nummy(min).format(format)
                + FWP.settings[facet_name]['suffix'];
        }
        else {
            var label = FWP.settings[facet_name]['prefix']
                + nummy(min).format(format)
                + FWP.settings[facet_name]['suffix']
                + ' &mdash; '
                + FWP.settings[facet_name]['prefix']
                + nummy(max).format(format)
                + FWP.settings[facet_name]['suffix'];
        }
        $this.find('.facetwp-slider-label').html(label);
    });

    $(document).on('facetwp-loaded', function() {
        $('.facetwp-slider:not(.ready)').each(function() {
            var $parent = $(this).closest('.facetwp-facet');
            var facet_name = $parent.attr('data-name');
            var opts = FWP.settings[facet_name];

            // Fail on slider already initialized
            if ('undefined' != typeof $(this).data('options')) {
                return;
            }

            // Fail on invalid ranges
            if (parseFloat(opts.range.min) >= parseFloat(opts.range.max)) {
                FWP.settings[facet_name]['lower'] = opts.range.min;
                FWP.settings[facet_name]['upper'] = opts.range.max;
                wp.hooks.doAction('facetwp/set_label/slider', $parent);
                return;
            }

            // Support custom slider options
            var slider_opts = wp.hooks.applyFilters('facetwp/set_options/slider', {
                range: opts.range,
                start: opts.start,
                step: parseFloat(opts.step),
                connect: true
            }, { 'facet_name': facet_name });


            $(this).noUiSlider(slider_opts)
            .Link('lower').to(function(val) {
                FWP.settings[facet_name]['lower'] = val;
                wp.hooks.doAction('facetwp/set_label/slider', $parent);
            })
            .Link('upper').to(function(val) {
                FWP.settings[facet_name]['upper'] = val;
                wp.hooks.doAction('facetwp/set_label/slider', $parent);
            })
            .on('set', function() {
                FWP.used_facets[facet_name] = true;
                FWP.static_facet = facet_name;
                FWP.autoload();
            });

            $(this).addClass('ready');
        });
    });

    $(document).on('click', '.facetwp-slider-reset', function() {
        var facet_name = $(this).closest('.facetwp-facet').attr('data-name');
        delete FWP.used_facets[facet_name];
        FWP.refresh();
    });
})(jQuery);
</script>
<?php
    }


    /**
     * (Admin) Output settings HTML
     */
    function settings_html() {
?>
        <tr class="facetwp-conditional type-slider">
            <td>
                <?php _e('Prefix', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Text that appears before each slider value', 'fwp' ); ?></div>
                </div>
            </td>
            <td><input type="text" class="facet-prefix" value="" /></td>
        </tr>
        <tr class="facetwp-conditional type-slider">
            <td>
                <?php _e('Suffix', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Text that appears after each slider value', 'fwp' ); ?></div>
                </div>
            </td>
            <td><input type="text" class="facet-suffix" value="" /></td>
        </tr>
        <tr class="facetwp-conditional type-slider">
            <td>
                <?php _e('Format', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The number format', 'fwp' ); ?></div>
                </div>
            </td>
            <td>
                <select class="facet-format">
                    <option value="0,0">5,280</option>
                    <option value="0,0.0">5,280.4</option>
                    <option value="0,0.00">5,280.42</option>
                    <option value="0">5280</option>
                    <option value="0.0">5280.4</option>
                    <option value="0.00">5280.42</option>
                    <option value="0a">5k</option>
                    <option value="0.0a">5.3k</option>
                    <option value="0.00a">5.28k</option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-slider">
            <td>
                <?php _e('Step', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The amount of increase between intervals', 'fwp' ); ?> (default = 1)</div>
                </div>
            </td>
            <td><input type="text" class="facet-step" value="1" /></td>
        </tr>
<?php
    }
}
