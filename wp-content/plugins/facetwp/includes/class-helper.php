<?php

final class FacetWP_Helper
{

    /* (array) The facetwp_settings option (after hooks) */
    public $settings;

    /* (array) Associative array of facet objects */
    public $facet_types;

    /* (array) Cached data sources */
    public $data_sources;


    function __construct() {
        $this->facet_types = $this->get_facet_types();
        $this->settings = $this->load_settings();
    }


    /**
     * Parse the URL hostname
     */
    function get_http_host() {
        return parse_url( get_option( 'home' ), PHP_URL_HOST );
    }


    /**
     * Get the current page URI
     */
    function get_uri() {
        $uri = $_SERVER['REQUEST_URI'];
        if ( false !== ( $pos = strpos( $uri, '?' ) ) ) {
            $uri = substr( $uri, 0, $pos );
        }
        return trim( $uri, '/' );
    }


    /**
     * Get available facet types
     */
    function get_facet_types() {
        if ( ! empty( $this->facet_types ) ) {
            return $this->facet_types;
        }

        include( FACETWP_DIR . '/includes/facets/base.php' );

        $types = [
            'checkboxes'    => 'Facetwp_Facet_Checkboxes',
            'dropdown'      => 'Facetwp_Facet_Dropdown',
            'fselect'       => 'Facetwp_Facet_fSelect',
            'hierarchy'     => 'Facetwp_Facet_Hierarchy',
            'search'        => 'Facetwp_Facet_Search',
            'autocomplete'  => 'Facetwp_Facet_Autocomplete',
            'slider'        => 'Facetwp_Facet_Slider',
            'date_range'    => 'Facetwp_Facet_Date_Range',
            'number_range'  => 'Facetwp_Facet_Number_Range',
            'proximity'     => 'Facetwp_Facet_Proximity_Core',
            'radio'         => 'Facetwp_Facet_Radio_Core',
            'rating'        => 'FacetWP_Facet_Rating'
        ];

        $facet_types = [];

        foreach ( $types as $slug => $class_name ) {
            include( FACETWP_DIR . "/includes/facets/$slug.php" );
            $facet_types[ $slug ] = new $class_name();
        }

        return apply_filters( 'facetwp_facet_types', $facet_types );
    }


    /**
     * Get settings and allow for developer hooks
     */
    function load_settings( $last_index = false ) {
        $name = $last_index ? 'facetwp_settings_last_index' : 'facetwp_settings';
        $option = get_option( $name );

        $defaults = [
            'facets' => [],
            'templates' => [],
            'settings' => [
                'thousands_separator' => ',',
                'decimal_separator' => '.',
                'loading_animation' => 'fade',
                'prefix' => 'fwp_'
            ]
        ];

        $settings = ( false !== $option ) ? json_decode( $option, true ) : [];
        $settings = array_merge( $defaults, $settings );
        $settings['settings'] = array_merge( $defaults['settings'], $settings['settings'] );

        // Store DB-based facet & template names
        $db_names = [];

        foreach ( $settings['facets'] as $facet ) {
            $db_names[ 'facet-' . $facet['name'] ] = true;
        }

        foreach ( $settings['templates'] as $template ) {
            $db_names[ 'template-' . $template['name'] ] = true;
        }

        // Programmatically registered
        $facets = apply_filters( 'facetwp_facets', $settings['facets'] );
        $templates = apply_filters( 'facetwp_templates', $settings['templates'] );

        $tmp_facets = [];
        $tmp_templates = [];

        // Merge DB + code-based facets
        foreach ( $facets as $facet ) {
            $name = $facet['name'];
            $is_db_based = isset( $db_names[ "facet-$name" ] );

            if ( ! $is_db_based ) {
                $facet['_code'] = true;
            }

            if ( ! $is_db_based || empty( $tmp_facets[ $name ] ) ) {

                // Valid facet type?
                if ( in_array( $facet['type'], array_keys( $this->facet_types ) ) ) {
                    $tmp_facets[ $name ] = $facet;
                }
            }
        }

        // Merge DB + code-based templates
        foreach ( $templates as $template ) {
            $name = $template['name'];
            $is_db_based = isset( $db_names[ "template-$name" ] );

            if ( ! $is_db_based ) {
                $template['_code'] = true;
            }

            if ( ! $is_db_based || empty( $tmp_templates[ $name ] ) ) {
                $tmp_templates[ $name ] = $template;
            }
        }

        // Convert back to numerical arrays
        $settings['facets'] = array_values( $tmp_facets );
        $settings['templates'] = array_values( $tmp_templates );

        // Filtered settings
        return $settings;
    }


    /**
     * Get a general setting value
     *
     * @param string $name The setting name
     * @param mixed $default The default value
     * @since 1.9
     */
    function get_setting( $name, $default = '' ) {
        if ( isset( $this->settings['settings'][ $name ] ) ) {
            return $this->settings['settings'][ $name ];
        }

        return $default;
    }


    /**
     * Get an array of all facets
     * @return array
     */
    function get_facets() {
        return $this->settings['facets'];
    }


    /**
     * Get an array of all templates
     * @return array
     */
    function get_templates() {
        return $this->settings['templates'];
    }


    /**
     * Get all properties for a single facet
     * @param string $facet_name
     * @return mixed An array of facet info, or false
     */
    function get_facet_by_name( $facet_name ) {
        foreach ( $this->get_facets() as $facet ) {
            if ( $facet_name == $facet['name'] ) {
                return $facet;
            }
        }

        return false;
    }


    /**
     * Get all properties for a single template
     *
     * @param string $template_name
     * @return mixed An array of template info, or false
     */
    function get_template_by_name( $template_name ) {
        foreach ( $this->get_templates() as $template ) {
            if ( $template_name == $template['name'] ) {
                return $template;
            }
        }

        return false;
    }


    /**
     * Fetch facets using one of its settings
     * @param string $setting_name
     * @param mixed $setting_value
     * @return array
     */
    function get_facets_by( $setting, $value ) {
        $matches = [];

        foreach ( $this->get_facets() as $facet ) {
            if ( isset( $facet[ $setting ] ) && $value === $facet[ $setting ] ) {
                $matches[] = $facet;
            }
        }

        return $matches;
    }


    /**
     * Get an array of term information, including depth
     * @param string $taxonomy The taxonomy name
     * @return array Term information
     * @since 0.9.0
     */
    function get_term_depths( $taxonomy ) {

        $output = [];
        $parents = [];

        $terms = get_terms( $taxonomy, [ 'hide_empty' => false ] );
        if ( is_wp_error( $terms ) ) {
            return $output;
        }

        // Get term parents
        foreach ( $terms as $term ) {
            $parents[ $term->term_id ] = $term->parent;
        }

        // Build the term array
        foreach ( $terms as $term ) {
            $output[ $term->term_id ] = [
                'term_id'       => $term->term_id,
                'name'          => $term->name,
                'slug'          => $term->slug,
                'parent_id'     => $term->parent,
                'depth'         => 0
            ];

            $current_parent = $term->parent;
            while ( 0 < (int) $current_parent ) {
                $current_parent = $parents[ $current_parent ];
                $output[ $term->term_id ]['depth']++;

                // Prevent an infinite loop
                if ( 50 < $output[ $term->term_id ]['depth'] ) {
                    break;
                }
            }
        }

        return $output;
    }


    /**
     * Finish sorting the facet values
     * The results are already sorted by depth and (name OR count), we just need
     * to move the children directly below their parents
     */
    function sort_taxonomy_values( $values = [], $orderby = 'count' ) {

        // Create an "order" sort value based on the top-level items
        $cache = [];
        foreach ( $values as $key => $val ) {
            if ( 0 == $val['depth'] ) {
                $cache[ $val['term_id'] ] = $key;
                $values[ $key ]['order'] = $key;
            }
            else {
                $new_order = $cache[ $val['parent_id'] ] . ".$key"; // dot-separated hierarchy string
                $cache[ $val['term_id'] ] = $new_order;
                $values[ $key ]['order'] = $new_order;
            }
        }

        // Sort the array based on the new "order" element
        // Since this is a dot-separated hierarchy string, treat it like version_compare
        usort( $values, function( $a, $b ) {
            return version_compare( $a['order'], $b['order'] );
        });

        return $values;
    }


    /**
     * Sanitize SQL data
     * @return mixed The sanitized value(s)
     * @since 3.0.7
     */
    function sanitize( $input ) {
        global $wpdb;

        if ( is_array( $input ) ) {
            $output = [];

            foreach ( $input as $key => $val ) {
                $output[ $key ] = $this->sanitize( $val );
            }
        }
        else {
            if ( $wpdb->dbh ) {
                if ( $wpdb->use_mysqli ) {
                    $output = mysqli_real_escape_string( $wpdb->dbh, $input );
                }
                else {
                    // Fallback for old PHP versions
                    $output = mysql_real_escape_string( $input, $wpdb->dbh );
                }
            }
            else {
                $output = addslashes( $input );
            }
        }

        return $output;
    }


    /**
     * Does an active facet with the specified setting exist?
     * @return boolean
     * @since 1.4.0
     */
    function facet_setting_exists( $setting_name, $setting_value ) {
        foreach ( FWP()->facet->facets as $f ) {
            if ( isset( $f[ $setting_name ] ) && $f[ $setting_name ] == $setting_value ) {
                return true;
            }
        }

        return false;
    }


    /**
     * Does this facet have a setting with the specified value?
     * @return boolean
     * @since 2.3.4
     */
    function facet_is( $facet, $setting_name, $setting_value ) {
        if ( is_string( $facet ) ) {
            $facet = $this->get_facet_by_name( $facet );
        }

        if ( isset( $facet[ $setting_name ] ) && $facet[ $setting_name ] == $setting_value ) {
            return true;
        }

        return false;
    }


    /**
     * Hash a facet value if needed
     * @return string
     * @since 2.1
     */
    function safe_value( $value ) {
        $value = remove_accents( $value );

        if ( preg_match( '/[^a-z0-9_.\- ]/i', $value ) ) {
            if ( ! preg_match( '/^\d{4}-(0[1-9]|1[012])-([012]\d|3[01])/', $value ) ) {
                $value = md5( $value );
            }
        }

        $value = str_replace( ' ', '-', strtolower( $value ) );
        $value = preg_replace( '/[-]{2,}/', '-', $value );
        $value = ( 50 < strlen( $value ) ) ? md5( $value ) : $value;
        return $value;
    }


    /**
     * Properly format numbers, taking separators into account
     * @return number
     * @since 2.7.5
     */
    function format_number( $num ) {
        $sep_decimal = $this->get_setting( 'decimal_separator' );
        $sep_thousands = $this->get_setting( 'thousands_separator' );

        $num = str_replace( $sep_thousands, '', $num );
        $num = ( ',' == $sep_decimal ) ? str_replace( ',', '.', $num ) : $num;
        $num = preg_replace( '/[^0-9-.]/', '', $num );

        return $num;
    }


    /**
     * Get facet data sources
     * @return array
     * @since 2.2.1
     */
    function get_data_sources() {

        // Return cached sources
        if ( ! empty( $this->data_sources ) ) {
            return $this->data_sources;
        }

        global $wpdb;

        // Get excluded meta keys
        $excluded_fields = apply_filters( 'facetwp_excluded_custom_fields', [
            '_edit_last',
            '_edit_lock',
        ] );

        // Get taxonomies
        $taxonomies = get_taxonomies( [], 'object' );

        // Get custom fields
        $meta_keys = $wpdb->get_col( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} ORDER BY meta_key" );
        $custom_fields = array_diff( $meta_keys, $excluded_fields );

        $sources = [
            'posts' => [
                'label' => __( 'Posts', 'fwp' ),
                'choices' => [
                    'post_type'         => __( 'Post Type', 'fwp' ),
                    'post_date'         => __( 'Post Date', 'fwp' ),
                    'post_modified'     => __( 'Post Modified', 'fwp' ),
                    'post_title'        => __( 'Post Title', 'fwp' ),
                    'post_author'       => __( 'Post Author', 'fwp' )
                ],
                'weight' => 10
            ],
            'taxonomies' => [
                'label' => __( 'Taxonomies', 'fwp' ),
                'choices' => [],
                'weight' => 20
            ],
            'custom_fields' => [
                'label' => __( 'Custom Fields', 'fwp' ),
                'choices' => [],
                'weight' => 30
            ]
        ];

        foreach ( $taxonomies as $tax ) {
            $sources['taxonomies']['choices'][ 'tax/' . $tax->name ] = $tax->labels->name;
        }

        foreach ( $custom_fields as $cf ) {
            if ( 0 !== strpos( $cf, '_oembed_' ) ) {
                $sources['custom_fields']['choices'][ 'cf/' . $cf ] = $cf;
            }
        }

        $sources = apply_filters( 'facetwp_facet_sources', $sources );

        uasort( $sources, [ $this, 'sort_by_weight' ] );

        $this->data_sources = $sources;

        return $sources;
    }


    /**
     * Sort facetwp_facet_sources by weight
     * @since 2.7.5
     */
    function sort_by_weight( $a, $b ) {
        $a['weight'] = isset( $a['weight'] ) ? $a['weight'] : 10;
        $b['weight'] = isset( $b['weight'] ) ? $b['weight'] : 10;

        if ( $a['weight'] == $b['weight'] ) {
            return 0;
        }

        return ( $a['weight'] < $b['weight'] ) ? -1 : 1;
    }


    /**
     * Get row counts for all facets
     * @since 3.3.4
     */
    function get_row_counts() {
        global $wpdb;

        $output = [];
        $results = $wpdb->get_results( "SELECT facet_name, COUNT(*) AS row_count FROM {$wpdb->prefix}facetwp_index GROUP BY facet_name" );

        foreach ( $results as $result ) {
            $output[ $result->facet_name ] = (int) $result->row_count;
        }

        return $output;
    }


    /**
     * Grab the license key
     * @since 3.0.3
     */
    function get_license_key() {
        $license_key = defined( 'FACETWP_LICENSE_KEY' ) ? FACETWP_LICENSE_KEY : get_option( 'facetwp_license' );
        $license_key = apply_filters( 'facetwp_license_key', $license_key );
        return sanitize_text_field( trim( $license_key ) );
    }


    /**
     * Determine whether the license is active
     * @since 3.3.0
     */
    function is_license_active() {
        $activation = get_option( 'facetwp_activation' );

        if ( ! empty( $activation ) ) {
            $activation = json_decode( $activation );

            if ( isset( $activation->status ) && 'success' == $activation->status ) {
                return true;
            }
        }

        return false;
    }
}
