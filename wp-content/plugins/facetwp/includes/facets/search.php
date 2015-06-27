<?php

class FacetWP_Facet_Search
{

    function __construct() {
        $this->label = __( 'Search', 'fwp' );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $value = (array) $params['selected_values'];
        $value = empty( $value ) ? '' : $value[0];
        $placeholder = isset( $params['facet']['placeholder'] ) ? $params['facet']['placeholder'] : __( 'Enter keywords', 'fwp' );
        $output .= '<input type="search" class="facetwp-search" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '" />';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;

        if ( empty( $selected_values ) ) {
            return 'continue';
        }

        // Default WP search
        if ( empty( $facet['search_engine'] ) ) {
            $search_args = array(
                's' => $selected_values,
                'posts_per_page' => 200,
                'fields' => 'ids',
            );

            $search_args = apply_filters( 'facetwp_search_query_args', $search_args, $params );

            $query = new WP_Query( $search_args );

            return (array) $query->posts;
        }
        // SearchWP
        else {
            // Return only post IDs and set pagination to 200
            add_filter( 'searchwp_load_posts', '__return_false' );
            add_filter( 'searchwp_posts_per_page', array( $this, 'searchwp_posts_per_page' ) );

            // Perform the search
            $searchwp = SearchWP::instance();
            $results = $searchwp->search( $facet['search_engine'], $selected_values, 1 );

            // Revert filters
            remove_filter( 'searchwp_load_posts', '__return_false' );
            remove_filter( 'searchwp_posts_per_page', array( $this, 'searchwp_posts_per_page' ) );

            return (array) $results;
        }
    }


    /**
     * Pagination callback for SearchWP
     */
    function searchwp_posts_per_page() {
        return 200;
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/search', function($this, obj) {
        $this.find('.facet-search-engine').val(obj.search_engine);
        $this.find('.facet-placeholder').val(obj.placeholder);
    });

    wp.hooks.addFilter('facetwp/save/search', function($this, obj) {
        obj['search_engine'] = $this.find('.facet-search-engine').val();
        obj['placeholder'] = $this.find('.facet-placeholder').val();
        return obj;
    });

    wp.hooks.addAction('facetwp/change/search', function($this) {
        $this.closest('.facetwp-facet').find('.name-source').hide();
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
    wp.hooks.addAction('facetwp/refresh/search', function($this, facet_name) {
        var val = $this.find('.facetwp-search').val() || '';
        FWP.facets[facet_name] = val;
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('keyup', '.facetwp-facet .facetwp-search', function(e) {
            if (13 == e.keyCode) {
                FWP.autoload();
            }
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
        $engines = array();
        if ( is_plugin_active( 'searchwp/searchwp.php' ) ) {
            $settings = get_option( SEARCHWP_PREFIX . 'settings' );
            $engines = $settings['engines'];
        }

?>
        <tr class="facetwp-conditional type-search">
            <td><?php _e('Search engine', 'fwp'); ?>:</td>
            <td>
                <select class="facet-search-engine">
                    <option value=""><?php _e( 'WP Default', 'fwp' ); ?></option>
                    <?php foreach ( $engines as $key => $attr ) : ?>
                    <?php $label = isset( $attr['searchwp_engine_label'] ) ? $attr['searchwp_engine_label'] : __( 'Default', 'fwp' ); ?>
                    <option value="<?php echo $key; ?>">SearchWP - <?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-search">
            <td><?php _e('Placeholder text', 'fwp'); ?>:</td>
            <td><input type="text" class="facet-placeholder" value="" /></td>
        </tr>
<?php
    }
}
