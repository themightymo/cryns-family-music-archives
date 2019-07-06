<?php

class FacetWP_Facet_Search extends FacetWP_Facet
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
        $value = empty( $value ) ? '' : stripslashes( $value[0] );
        $placeholder = isset( $params['facet']['placeholder'] ) ? $params['facet']['placeholder'] : __( 'Enter keywords', 'fwp-front' );
        $placeholder = facetwp_i18n( $placeholder );
        $output .= '<span class="facetwp-search-wrap">';
        $output .= '<i class="facetwp-btn"></i>';
        $output .= '<input type="text" class="facetwp-search" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '" />';
        $output .= '</span>';
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
        $search_args = [
            's' => $selected_values,
            'posts_per_page' => 200,
            'fields' => 'ids',
        ];

        $search_args = apply_filters( 'facetwp_search_query_args', $search_args, $params );

        $query = new WP_Query( $search_args );

        return (array) $query->posts;
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
        $engines = apply_filters( 'facetwp_facet_search_engines', [] );
?>
        <div class="facetwp-row">
            <div><?php _e('Search engine', 'fwp'); ?>:</div>
            <div>
                <select class="facet-search-engine">
                    <option value=""><?php _e( 'WP Default', 'fwp' ); ?></option>
                    <?php foreach ( $engines as $key => $label ) : ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="facetwp-row">
            <div><?php _e( 'Placeholder text', 'fwp' ); ?>:</div>
            <div><input type="text" class="facet-placeholder" /></div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e('Auto refresh', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Automatically refresh the results while typing?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-auto-refresh" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
<?php
    }


    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        $auto_refresh = empty( $params['facet']['auto_refresh'] ) ? 'no' : $params['facet']['auto_refresh'];
        return [ 'auto_refresh' => $auto_refresh ];
    }
}
