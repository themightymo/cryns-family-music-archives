<?php

class FacetWP_Facet_Checkboxes
{

    function __construct() {
        $this->label = __( 'Checkboxes', 'fwp' );

        add_filter( 'facetwp_pre_filtered_post_ids', array( $this, 'save_unfiltered_post_ids' ), 20, 2 );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $where_clause = $params['where_clause'];

        // Orderby
        $orderby = 'counter DESC, f.facet_display_value ASC';
        if ( 'display_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_display_value ASC';
        }
        elseif ( 'raw_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_value ASC';
        }

        // Sort by depth just in case
        $orderby = "f.depth, $orderby";

        // Limit
        $limit = ctype_digit( $facet['count'] ) ? $facet['count'] : 10;

        // Properly handle "OR" facets
        if ( 'or' == $facet['operator'] ) {

            // Apply filtering (ignore the current facet's selections)
            if ( ! empty( FWP()->or_values ) && ( 1 < count( FWP()->or_values ) || ! isset( FWP()->or_values[ $facet['name'] ] ) ) ) {
                $post_ids = array();
                $or_values = FWP()->or_values; // Preserve the original
                unset( $or_values[ $facet['name'] ] );

                $counter = 0;
                foreach ( $or_values as $name => $vals ) {
                    $post_ids = ( 0 == $counter ) ? $vals : array_intersect( $post_ids, $vals );
                    $counter++;
                }

                // Return only applicable results
                $post_ids = array_intersect( $post_ids, FWP()->unfiltered_post_ids );
            }
            else {
                $post_ids = FWP()->unfiltered_post_ids;
            }

            $post_ids = empty( $post_ids ) ? array( 0 ) : $post_ids;
            $where_clause = ' AND post_id IN (' . implode( ',', $post_ids ) . ')';
        }

        $orderby = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );
        $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );

        $sql = "
        SELECT f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, COUNT(*) AS counter
        FROM {$wpdb->prefix}facetwp_index f
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY f.facet_value
        ORDER BY $orderby
        LIMIT $limit";

        $output = $wpdb->get_results( $sql, ARRAY_A );

        // Show "ghost" facet choices (those that return zero results)
        if ( 'yes' == $facet['ghosts'] && ! empty( $this->unfiltered_ids ) ) {
            $unfiltered_ids = implode( ',', $this->unfiltered_ids );

            $sql = "
            SELECT f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, 0 AS counter
            FROM {$wpdb->prefix}facetwp_index f
            WHERE f.facet_name = '{$facet['name']}' AND post_id IN ($unfiltered_ids)
            GROUP BY f.facet_value
            ORDER BY $orderby
            LIMIT $limit";

            $ghost_output = $wpdb->get_results( $sql, ARRAY_A );

            // Keep the facet placement intact
            if ( 'yes' == $facet['preserve_ghosts'] ) {
                $tmp = array();
                foreach ( $ghost_output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row;
                }

                foreach ( $output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row;
                }

                $output = $tmp;
            }
            else {
                // Make the array key equal to the facet_value (for easy lookup)
                $tmp = array();
                foreach ( $output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row; // Force a string array key
                }
                $output = $tmp;

                foreach ( $ghost_output as $row ) {
                    $facet_value = $row['facet_value'];
                    if ( ! isset( $output[ "$facet_value " ] ) ) {
                        $output[ "$facet_value " ] = $row;
                    }
                }
            }

            $output = array_splice( $output, 0, $limit );
            $output = array_values( $output );
        }

        return $output;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $facet = $params['facet'];

        if ( isset( $facet['hierarchical'] ) && 'yes' == $facet['hierarchical'] ) {
            return $this->render_hierarchy( $params );
        }

        $output = '';
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        foreach ( $values as $result ) {
            $selected = in_array( $result['facet_value'], $selected_values ) ? ' checked' : '';
            $selected .= ( 0 == $result['counter'] ) ? ' disabled' : '';
            $output .= '<div class="facetwp-checkbox' . $selected . '" data-value="' . $result['facet_value'] . '">';
            $output .= $result['facet_display_value'] . ' <span class="facetwp-counter">(' . $result['counter'] . ')</span>';
            $output .= '</div>';
        }

        return $output;
    }


    /**
     * Generate the facet HTML (hierarchical taxonomies)
     */
    function render_hierarchy( $params ) {

        $output = '';
        $facet = $params['facet'];
        $selected_values = (array) $params['selected_values'];
        $values = FWP()->helper->sort_taxonomy_values( $params['values'], $facet['orderby'] );

        $last_depth = 0;
        foreach ( $values as $result ) {
            $depth = (int) $result['depth'];

            if ( $depth > $last_depth ) {
                $output .= '<div class="facetwp-depth">';
            }
            elseif ( $depth < $last_depth ) {
                for ( $i = $last_depth; $i > $depth; $i-- ) {
                    $output .= '</div>';
                }
            }

            $selected = in_array( $result['facet_value'], $selected_values ) ? ' checked' : '';
            $selected .= ( 0 == $result['counter'] ) ? ' disabled' : '';
            $output .= '<div class="facetwp-checkbox' . $selected . '" data-value="' . $result['facet_value'] . '">';
            $output .= $result['facet_display_value'] . ' <span class="facetwp-counter">(' . $result['counter'] . ')</span>';
            $output .= '</div>';

            $last_depth = $depth;
        }

        for ( $i = $last_depth; $i > 0; $i-- ) {
            $output .= '</div>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $output = array();
        $facet = $params['facet'];
        $selected_values = $params['selected_values'];

        $sql = $wpdb->prepare( "SELECT DISTINCT post_id
            FROM {$wpdb->prefix}facetwp_index
            WHERE facet_name = %s",
            $facet['name']
        );

        // Match ALL values
        if ( 'and' == $facet['operator'] ) {
            foreach ( $selected_values as $key => $value ) {
                $results = $wpdb->get_col( $sql . " AND facet_value IN ('$value')" );
                $output = ( $key > 0 ) ? array_intersect( $output, $results ) : $results;

                if ( empty( $output ) ) {
                    break;
                }
            }
        }
        // Match ANY value
        else {
            $selected_values = implode( "','", $selected_values );
            $output = $wpdb->get_col( $sql . " AND facet_value IN ('$selected_values')" );
        }

        return $output;
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/checkboxes', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.facet-parent-term').val(obj.parent_term);
        $this.find('.type-checkboxes .facet-orderby').val(obj.orderby);
        $this.find('.type-checkboxes .facet-operator').val(obj.operator);
        $this.find('.type-checkboxes .facet-hierarchical').val(obj.hierarchical);
        $this.find('.type-checkboxes .facet-ghosts').val(obj.ghosts);
        $this.find('.type-checkboxes .facet-preserve-ghosts').val(obj.preserve_ghosts);
        $this.find('.type-checkboxes .facet-count').val(obj.count);
    });

    wp.hooks.addFilter('facetwp/save/checkboxes', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['parent_term'] = $this.find('.type-checkboxes .facet-parent-term').val();
        obj['orderby'] = $this.find('.type-checkboxes .facet-orderby').val();
        obj['operator'] = $this.find('.type-checkboxes .facet-operator').val();
        obj['hierarchical'] = $this.find('.type-checkboxes .facet-hierarchical').val();
        obj['ghosts'] = $this.find('.type-checkboxes .facet-ghosts').val();
        obj['preserve_ghosts'] = $this.find('.type-checkboxes .facet-preserve-ghosts').val();
        obj['count'] = $this.find('.type-checkboxes .facet-count').val();
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
    wp.hooks.addAction('facetwp/refresh/checkboxes', function($this, facet_name) {
        var selected_values = [];
        $this.find('.facetwp-checkbox.checked').each(function() {
            selected_values.push($(this).attr('data-value'));
        });
        FWP.facets[facet_name] = selected_values;
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('click', '.facetwp-facet .facetwp-checkbox:not(.disabled)', function() {
            $(this).toggleClass('checked');
            var $facet = $(this).closest('.facetwp-facet');
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
?>
        <tr class="facetwp-conditional type-checkboxes">
            <td>
                <?php _e('Parent term', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">
                        If <strong>Data source</strong> is a taxonomy, enter the
                        parent term's ID if you want to show child terms.
                        Otherwise, leave blank.
                    </div>
                </div>
            </td>
            <td>
                <input type="text" class="facet-parent-term" value="" />
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes">
            <td><?php _e('Sort by', 'fwp'); ?>:</td>
            <td>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Facet Count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display Value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw Value', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes">
            <td>
                <?php _e('Behavior', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'How should multiple selections affect the results?', 'fwp' ); ?></div>
                </div>
            </td>
            <td>
                <select class="facet-operator">
                    <option value="and"><?php _e( 'Narrow the result set', 'fwp' ); ?></option>
                    <option value="or"><?php _e( 'Widen the result set', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes">
            <td>
                <?php _e('Hierarchical', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Is this a hierarchical taxonomy?', 'fwp' ); ?></div>
                </div>
            </td>
            <td>
                <select class="facet-hierarchical">
                    <option value="no"><?php _e( 'No', 'fwp' ); ?></option>
                    <option value="yes"><?php _e( 'Yes', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes">
            <td>
                <?php _e('Show ghosts', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Show choices that would return zero results?', 'fwp' ); ?></div>
                </div>
            </td>
            <td>
                <select class="facet-ghosts">
                    <option value="no"><?php _e( 'No', 'fwp' ); ?></option>
                    <option value="yes"><?php _e( 'Yes', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes">
            <td>
                <?php _e('Preserve ghost order', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Keep ghost choices in the same order?', 'fwp' ); ?></div>
                </div>
            </td>
            <td>
                <select class="facet-preserve-ghosts">
                    <option value="no"><?php _e( 'No', 'fwp' ); ?></option>
                    <option value="yes"><?php _e( 'Yes', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-checkboxes">
            <td>
                <?php _e('Count', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The maximum number of facet choices to show', 'fwp' ); ?></div>
                </div>
            </td>
            <td><input type="text" class="facet-count" value="10" /></td>
        </tr>
<?php
    }


    /**
     * For ghost facets, get all default facet options
     */
    function save_unfiltered_post_ids( $post_ids, $class ) {
        foreach ( $class->facets as $f ) {
            if ( isset( $f['ghosts'] ) && 'yes' == $f['ghosts'] ) {
                $this->unfiltered_ids = $post_ids;
                break;
            }
        }

        return $post_ids;
    }
}
