<?php

class FacetWP_Integration_SearchWP
{

    function __construct() {
        add_filter( 'facetwp_query_args', array( $this, 'search_args' ), 10, 2 );
        add_filter( 'facetwp_filtered_post_ids', array( $this, 'searchwp_search' ), 10, 2 );
    }


    /**
     * Prevent the default WP search from running when SearchWP is enabled
     * @since 1.3.2
     */
    function search_args( $args, $class ) {

        if ( ! empty( $args['s'] ) ) {
            $class->is_search = true;

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            if ( is_plugin_active( 'searchwp/searchwp.php' ) ) {
                $class->search_terms = $args['s'];
                unset( $args['s'] );

                $args['suppress_filters'] = true;
                if ( empty( $args['post_type'] ) ) {
                    $args['post_type'] = 'any';
                }
            }
        }

        return $args;
    }


    /**
     * Use the SearchWP API to retrieve matching post IDs
     * @since 1.3.2
     */
    function searchwp_search( $post_ids, $class ) {

        if ( ! empty( $class->search_terms ) && class_exists( 'SearchWP' ) ) {

            // Return only post IDs and set pagination to 200
            add_filter( 'searchwp_load_posts', '__return_false' );
            add_filter( 'searchwp_posts_per_page', array( $this, 'searchwp_posts_per_page' ) );

            // Perform the search
            $searchwp = SearchWP::instance();
            $results = $searchwp->search( 'default', $class->search_terms, 1 );

            // Revert filters
            remove_filter( 'searchwp_load_posts', '__return_false' );
            remove_filter( 'searchwp_posts_per_page', array( $this, 'searchwp_posts_per_page' ) );

            // Preserve post ID order
            $intersected_ids = array();
            foreach ( $results as $post_id ) {
                if ( in_array( $post_id, $post_ids ) ) {
                    $intersected_ids[] = $post_id;
                }
            }
            $post_ids = $intersected_ids;
        }

        return empty( $post_ids ) ? array( 0 ) : $post_ids;
    }


    /**
     * SearchWP pagination callback
     * @since 1.7.1
     */
    function searchwp_posts_per_page() {
        return 200;
    }
}


new FacetWP_Integration_SearchWP();
