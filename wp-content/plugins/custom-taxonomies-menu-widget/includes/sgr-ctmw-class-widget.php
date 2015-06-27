<?php
/**
 * SGR_Widget_Custom_Taxonomies_Menu class
 *
 * @author Ade WALKER  (email : info@studiograsshopper.ch)
 * @copyright Copyright 2010-2013
 * @package custom_taxonomies_menu_widget
 * @version 1.3.1
 *
 * Defines widget class and registers widget
 * Any helper functions outside the class, but used by the class, are also defined here
 *
 * Since 1.3, the way terms are selected by the widget has changed, as described below.
 *
 * Recap: Version 1.2 introduced automatic inclusion of new terms added to an existing taxonomy
 * since the last time the widget was Saved. This enabled users to not having to open the
 * widget control panel in order to manually check a new term or for it to appear in the front
 * end Menu. This works great for hierarchical menus where it is logical that any new term is
 * automatically displayed (and auto checked in the control panel).
 * However, some users want to mix and match terms from a taxonomy without caring
 * about the parent->child relationship between terms, or use multiple CTMW widgets and don't
 * want a child term appearing automatically unless its parent is checked.
 * Hence new 1.3 options and logic...
 *
 * Version 1.3 introduces new Terms Handling options:
 *	- Auto
 *		-	any new Top Level term added since last Save is:
 *			-	automatically included in Menu
 *			-	automatically shown as checked in the control panel
 *		-	any new Child term added since last Save is
 *			-	automatically included in Menu
 *			-	automatically shown as checked in the control panel
 *		-	Note that if this is the only TL Term, it will always be
 *			checked. Therefore, as with 1.2, to hide a Taxonomy uncheck
 *			the Taxonomy!
 *
 *	- Manual
 *		-	any new Top Level term added since last Save is:
 *			-	not included in Menu
 *			-	not shown as checked in the control panel
 *		-	any new Child term added since last Save is
 *			-	not included in Menu
 *			-	not shown as checked in the control panel
 *		-	User always has to open control panel to manually add terms
 *
 *	- TL / Smart Child
 *		-	any new Top Level term added since last Save is:
 *			-	automatically included in Menu
 *			-	automatically shown as checked in the control panel
 *		-	any new Child term added since last Save is:
 *			-	only included in Menu if its parent is already included
 *			-	only shown as checked in control panel if its parent is checked
 *		-	This means that a new child of a new Top Level term will be checked
 *			and included in Menu.
 *		-	On Save, any checked child terms whose parents have been unchecked in control
 *			panel will be automatically unchecked. In other words, to check a child
 *			term, you must also check its parent
 *
 *	- Man TL / Smart Child
 *		-	any new Top Level term added since last Save is:
 *			-	not included in Menu
 *			-	not shown as checked in the control panel
 *		-	any new Child term added since last Save is:
 *			-	only included in Menu if its parent is already included
 *			-	only shown as checked in control panel if its parent is checked
 *		-	This means that a new child of a new Top Level term will not be checked
 *			and won't be included in Menu.
 *		-	On Save, any checked child terms whose parents have been unchecked in control
 *			panel will be automatically unchecked. In other words, to check a child
 *			term, you must also check its parent
 *
 *	- To replicate pre-1.3 behaviour:
 *		-	Select AUTO
 *		-	However, note that TL / Samrt Child is probably the better option!
 *
 *
 *
 * @since 1.2
 */

 
/**
 * Prevent direct access to this file
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( _( 'Sorry, you are not allowed to access this file directly.' ) );
}



/**
 * Our widget class
 *
 *
 * @since 1.0
 */
class SGR_Widget_Custom_Taxonomies_Menu extends WP_Widget {

	function __construct() {
		
		$id_base = 'sgr-custom-taxonomies-menu';
		
		$widget_ops = array(
			'classname' => $id_base,
			'description' => __( 'Display navigation for your custom taxonomies.', 'sgr-ctmw' )
			);
			
		$control_ops = array(
			'id_base' => $id_base,
			'width'   => 505,
			'height'  => 350,
		);
		
		$this->WP_Widget( $id_base, __( 'Custom Taxonomies Menu Widget', 'sgr-ctmw'), $widget_ops, $control_ops );
	}
	
	
	/**
	 * Get all custom taxonomies on this install
	 *
	 * The 'sgr_ctmw_taxonomies' filter allows users to override the $args sent to
	 * the get_taxonomies() function, eg to include builtin taxonomies, etc.
	 *
	 * @since 1.2
	 * @updated 1.3
	 *
	 * @return Object of all custom taxonomies if there are any, or false if no taxonomies
	 */
	function taxonomies() {
	 
		$args = apply_filters( 'sgr_ctmw_taxonomies', array(
  			'public'   => true,
  			'_builtin' => false
			) );
			
		$output = 'objects'; // or names
		$operator = 'and'; // 'and' or 'or'
		$custom_taxonomies = get_taxonomies( $args, $output, $operator );
		
		return $custom_taxonomies;
	}
	
	
	/**
	 * Default widget args
	 *
	 * Sets defaults and merges them with current $instance settings
	 *
	 *
	 * Note: 'include' and 'known' are placeholders for the array of selected taxonomy terms
	 * which will be passed to wp_list_categories for each taxonomy ('include'), and the
	 * array of all existing terms ('known')
	 *
	 * Additional settings are dynamically created by the class:
	 * $instance['include_'.$custom_tax->name], array for each taxonomy, containing its selected terms
	 * $instance['known_'.$custom_tax->name], array for each taxonomy, containing all existing terms
	 * $instance['show_tax_'.$custom_tax->name], string, "true" if tax name is checked in the widget form
	 *
	 * @since 1.2
	 * @updated 1.3
	 *
	 * @param $instance, current $instance settings
	 * @return $instance, object of widget defaults merged with current $instance settings
	 */
	function defaults( $instance ) {

		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',				// string	Widget title to be displayed
			'include' => array(),		// array	Placeholder for the selected terms used in wp_list_categories
			'known' => array(),			// array	Placeholder for the terms known last time widget form was saved
			'order' => '',				// string	Arg for wp_list_categories	ASC or DESC
			'orderby' => '',			// string	Arg for wp_list_categories	name, id, slug, count, term_group
			'show_count' => '',			// on/off	Arg for wp_list_categories
			'show_tax_title' => '',		// on/off	Arg for wp_list_categories
			'show_hierarchical' => '',	// on/off	Arg for wp_list_categories
			'hide_empty' => '',			// on/off	Arg for wp_list_categories
			'terms_handling' => 'auto',	// string	auto, manual, tl-child, ntl-child
		) );
		
		return $instance;
	}
	
	/**
	 * Echo the custom taxonomies menu
	 *
	 * @since 1.0
	 * @updated 1.3
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {
		
		extract( $args );
		
		// Get defaults
		$instance = $this->defaults( $instance );
		
		echo $before_widget;
		
		if ( $instance['title'] ) echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;
			
		// Get taxonomies
		$custom_taxonomies = $this->taxonomies(); 
		
		// If no custom taxonomies exist...
		if( !$custom_taxonomies ) {
			echo "\n" . printf( '<p>%s</p>', __( 'There are no registered custom taxonomies.', 'sgr-ctmw' ) ) . "\n";
  			echo $after_widget;
  			return;
  		}
  			
  		// Loop through each taxonomy and display its terms using wp_list_categories
  		foreach ( $custom_taxonomies as $custom_tax ) {
  				
  			// Terms will only be displayed if Taxonomy has been checked in widget control panel
  			if( isset( $instance['show_tax_' . $custom_tax->name] ) && $instance['show_tax_' . $custom_tax->name] == "true" ) {
  				
  				// Get all terms that currently exist now, for this custom taxonomy
				$current_terms = get_terms( $custom_tax->name, array( 'hide_empty' => 0 ) );
  				
  				// Need to check whether any new terms have been added since the widget form was last saved
  				if ( isset( $instance['known_' . $custom_tax->name] ) && ! empty ( $instance['known_' . $custom_tax->name] ) ) {
  				
  					// Loop through all existing terms and look for newly added ones
  					foreach ( $current_terms as $current_term ) {
  					
  						// Do we have a new term added since the widget form was last saved?
  						if( ! in_array( $current_term->term_id, $instance['known_' . $custom_tax->name] ) ) {
  					
  							$parent_id = $current_term->parent;
  						
  							// Terms handling
  							// Auto -> Add in the term, regardless of top level or child
  							if ( $instance['terms_handling'] == 'auto' ) {
  								$instance['include_' . $custom_tax->name][] = $current_term->term_id;
  								continue;
  							}
  							
  							// Manual -> Ignore term, user needs to do checking himself
  							if ( $instance['terms_handling'] == 'manual' ) {
  								continue;
  							}
  							
  							// TL / Smart Child -> Add in the Top Level term
  							if ( $parent_id == '0' && $instance['terms_handling'] == 'tl-child' ) {
  								$instance['include_' . $custom_tax->name][] = $current_term->term_id;
  								continue;
  							}
  							
  							// No TL / Smart Child -> Ignore if a Top Level term
  							if ( $parent_id == '0' && $instance['terms_handling'] == 'ntl-child' ) {
  								continue;
  							}
  							
  							// Must be a child term and Smart Child method active
  							// therefore should we include the new child term?	
  							if ( in_array( $parent_id, $instance['include_' . $custom_tax->name] ) ) {
  						
  								// This is a child term of a checked parent
  								// Therefore add new term to the $instance['include_' . $custom_tax->name] array
  								$instance['include_' . $custom_tax->name][] = $current_term->term_id;
  						
  							}
  						}
  					}
				} // end new terms check
  				
  				// We're good to go, let's build the menu
  				$args_list = array(
  					'taxonomy' => $custom_tax->name, // Registered tax name
  					'title_li' => $instance['show_tax_title'] ? $custom_tax->labels->name : '', // Tax nice name
  					'include' => implode( ',', ( array )$instance['include_' . $custom_tax->name] ), // Selected terms
  					'orderby' => $instance['orderby'],
  					'show_count' => $instance['show_count'],
  					'order' => $instance['order'],
  					'echo' => '0',
					'hierarchical' => $instance['show_hierarchical'] ? true : false,
					'hide_empty' => $instance['hide_empty'] ? true : false,
  				 	);
  					 
  				$list = wp_list_categories( $args_list );
  				
  				echo "\n" . '<ul>' . "\n";
  				
  				echo $list;
  				  				
  				echo "\n" . '</ul>' . "\n";
  			
  			} // end isset(show_tax_ )
   		
   		} // end foreach   
				
		echo $after_widget;
	}

	/**
	 * Update instance options when form is saved.
	 *
	 * First, we run some sanitisation on user input
	 * Second, we loop through all custom taxonomies and:
	 *	-	add all existing terms to known_array
	 *	-	if Top Level Term, nothing to do here
	 *	-	if Auto Checking, remove any checked child terms whose parent has been
	 *		unchecked prior to Save
	 *
	 * The 'known_' array is saved and used on output to see if any new term added
	 * The 'include_' array is saved and used to display Menu on output
	 *
	 * @see WP_Widget::update()
	 *
	 * @since 1.0
	 * @updated 1.3
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		
		// Sanitise and validate
		$new_instance['title'] = esc_attr( $new_instance['title'] );
		
		$orderby = array( 'name', 'ID', 'slug', 'count', 'term_group' );
		$new_instance['orderby'] = in_array( $new_instance['orderby'], $orderby ) ? $new_instance['orderby'] : 'name';
		
		$order = array( 'ASC', 'DESC' );
		$new_instance['order'] = in_array( $new_instance['order'], $order ) ? $new_instance['order'] : 'ASC';
		
		$new_instance['show_count'] = $new_instance['show_count'] == 'true' ? 'true' : 0;
		$new_instance['hide_empty'] = $new_instance['hide_empty'] == 'true' ? 'true' : 0;
		$new_instance['show_tax_title'] = $new_instance['show_tax_title'] == 'true' ? 'true' : 0;
		$new_instance['show_hierachical'] = $new_instance['show_hierachical'] == 'true' ? 'true' : 0;
		
		$handling = array( 'auto', 'manual', 'tl-child', 'ntl-child' );
		$new_instance['terms_handling'] = in_array( $new_instance['terms_handling'], $handling ) ? $new_instance['terms_handling'] : 'auto';
		
		
		
		// Get all custom taxonomies
		$custom_taxonomies = $this->taxonomies();
		
		foreach( $custom_taxonomies as $custom_tax ) {
		
			// Update the known_ array with all current terms
			$current_terms = get_terms( $custom_tax->name, array( 'hide_empty' => 0 ) );
  				
  			// Update the known_ array with all current terms
  			foreach ( $current_terms as $current_term ) {
  				
  				// Store them in the ['known_' . $custom_tax->name] array for later use on output of widget
  				$new_instance['known_' . $custom_tax->name][] = $current_term->term_id;
  				
  				$parent_id = $current_term->parent;
  				
  				// Terms handling
  				// Nothing to do if not a Smart Child method
  				if ( $new_instance['terms_handling'] == 'auto' || $new_instance['terms_handling'] == 'manual')
  					continue;
  				
  				// Nothing to do if this is a top level term	
  				if ( $parent_id == '0' )
  					continue;
  				
  				// This is a child term and Smart Child method
  				// Need to uncheck this child term if its parent has just been unchecked
  				// Test if this child term's parent has been unchecked AND child term is checked
  				if ( ! in_array( $parent_id, $new_instance['include_' . $custom_tax->name] ) &&
  						in_array( $current_term->term_id, $new_instance['include_' . $custom_tax->name] ) ) {
  					
  					// Parent is unchecked, therefore remove this checked child from include_
  					if ( ( $key = array_search( $current_term->term_id, $new_instance['include_' . $custom_tax->name] ) ) !== false ) {
    					unset( $new_instance['include_' . $custom_tax->name][$key] );
					}
  				}
  			}
  		} // end taxonomy foreach loop
  		
		return $new_instance;
	}

	
	/**
	 * Output the widget control panel form
	 *
	 * @since 1.0
	 * @updated 1.3
	 *
	 * @param array $instance The settings for the particular instance of the widget
	 * @return string Outputs widget form contents
	 */
	function form( $instance ) { 
		
		// Load plugin textdomain
		sgr_ctmw_load_textdomain();
		
		// Get all custom taxonomies - shame we have to do this again, but we need it a few times below
		$custom_taxonomies = $this->taxonomies();
		
		if( !$custom_taxonomies ) {
			echo __( 'There are no custom taxonomies registered.', 'sgr-ctmw' );
			return;	
		}
		
		// Get parsed defaults - prevents PHP undefined index warnings
		$instance = $this->defaults( $instance );
		
		
		// Loop through all taxonomies and deal with two cases:
		//		Case 1: first use of widget = auto check all existing terms
		//		Case 2:	whether or not to auto check any new terms which have been added since widget last Saved
		// 
		// For Case 1, as the update() function deals with the user unchecking all terms in a taxonomy,
		// all we need do here is check if include_$custom_tax->name is empty and, if yes, it's a "first use"
		// scenario and we add all terms to the known_ and include_ arrays.
		// Note: if all terms for a taxonomy are unchecked by user, Case 1 will automatically re-check all terms
		// Therefore, to hide a taxonomy, user must uncheck the taxonomy, not all of the taxonomy's terms. Make sense?
		//
		// For Case 2, find out if a term has been added since last Save (ie, it isn't in the known_ array)
		// then only add to include_ array if parent is already in the known_ array
		foreach( $custom_taxonomies as $custom_tax ) {
		
  			// Get all terms that currently exist right now, for this custom taxonomy
			$current_terms = get_terms( $custom_tax->name, array( 'hide_empty' => 0 ) );
			
			// Case 1: first use - auto check all terms
			if( empty( $instance['include_' . $custom_tax->name] ) ) {

				foreach( $current_terms as $current_term ) {
				
					// Check all terms in this taxonomy
					$instance['include_' . $custom_tax->name][] = $current_term->term_id;
					
					// Add all terms as "known"
					$instance['known_' . $custom_tax->name][] = $current_term->term_id;
				}
			}
			
			// Populate the 'show_tax' taxonomy checkboxes to prevent PHP undefined index warnings
			if( empty( $instance['show_tax_' . $custom_tax->name] ) ) {
				
				// This is temporary. Only "true" will be saved by the form
				$instance['show_tax_' . $custom_tax->name] = "false";
			}
			
			// Case 2: deal with any new terms added since last save
			// Make sure we have known_ terms
  			if ( ! empty ( $instance['known_' . $custom_tax->name] ) ) {
  				
  				// Loop through all existing terms and look for newly added ones
  				foreach ( $current_terms as $current_term ) {
  					
  					// Do we have a new term added since the widget form was last saved?
  					if( ! in_array( $current_term->term_id, $instance['known_' . $custom_tax->name] ) ) {
  					
  						$parent_id = $current_term->parent;
  						
  						// Terms handling
  						// Auto -> Add in the term, regardless of top level or child
  						if ( $instance['terms_handling'] == 'auto' ) {
  							$instance['include_' . $custom_tax->name][] = $current_term->term_id;
  							continue;
  						}
  							
  						// Manual -> Ignore term, user needs to do checking himself
  						if ( $instance['terms_handling'] == 'manual' ) {
  							continue;
  						}
  							
  						// TL / Smart Child -> Add in the Top Level term
  						if ( $parent_id == '0' && $instance['terms_handling'] == 'tl-child' ) {
  							$instance['include_' . $custom_tax->name][] = $current_term->term_id;
  							continue;
  						}
  							
  						// No TL / Smart Child -> Ignore if a Top Level term
  						if ( $parent_id == '0' && $instance['terms_handling'] == 'ntl-child' ) {
  							continue;
  						}
  							
  						// Must be a child term and Smart Child method active
  						// therefore should we include the new child term?	
  						if ( in_array( $parent_id, $instance['include_' . $custom_tax->name] ) ) {
  						
  							// This is a child term of a checked parent
  							// Therefore add new term to the $instance['include_' . $custom_tax->name] array
  							$instance['include_' . $custom_tax->name][] = $current_term->term_id;
  						
  						}
  					}
  				}
			}
		}
		?>
		
		<div class="custom-taxonomies-menu-top">
			
			<p><?php _e( 'This widget produces a custom taxonomy navigation menu, ideal for use in sidebars.', 'sgr-ctmw' ); ?></p>
			<p>
				<a href="<?php echo SGR_CTMW_HOME; ?>"><?php _e( 'Plugin homepage', 'sgr-ctmw' ); ?></a> |
				<a href="<?php echo SGR_CTMW_HOME; ?>"><?php _e( 'FAQ', 'sgr-ctmw' ); ?></a> |
				<?php printf( __( 'version %s', 'sgr-ctmw' ), SGR_CTMW_VER ); ?>
			</p>
		
		</div>
		
		<div class="custom-taxonomies-menu-column">
		
			<div class="custom-taxonomies-menu-column-inner">
		
				<h4><?php _e( 'Display options', 'sgr-ctmw' ); ?></h4>
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>">
					<?php _e( 'Menu Title', 'sgr-ctmw' ); ?>:
					</label>
					<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
				</p>
		
				<p>
				<label for="<?php echo $this->get_field_name( 'orderby' ); ?>"><?php _e('Orderby:'); ?></label>
				<select name="<?php echo $this->get_field_name( 'orderby' ); ?>" class="widefat">
					<option style="padding-right:10px;" value="name" <?php selected('name', $instance['orderby']); ?>>Name</option>
					<option style="padding-right:10px;" value="ID" <?php selected('ID', $instance['orderby']); ?>>ID</option>
					<option style="padding-right:10px;" value="slug" <?php selected('slug', $instance['orderby']); ?>>Slug</option>
					<option style="padding-right:10px;" value="count" <?php selected('count', $instance['orderby']); ?>>Count</option>
					<option style="padding-right:10px;" value="term_group" <?php selected('term_group', $instance['orderby']); ?>>Term Group</option>
				</select>
				</p>
				
				<p>
				<label for="<?php echo $this->get_field_name( 'order' ); ?>"><?php _e('Order:'); ?></label>
				<select name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat">
					<option style="padding-right:10px;" value="ASC" <?php selected('ASC', $instance['order']); ?>>ASC (default)</option>
					<option style="padding-right:10px;" value="DESC" <?php selected('DESC', $instance['order']); ?>>DESC</option>
				</select>
				</p>
		
				<p>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="true" <?php checked( 'true', $instance['show_count'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">
						<?php _e( 'Show post count?', 'sgr-ctmw' ); ?>
					</label>
				</p>
				
				<p>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" value="true" <?php checked( 'true', $instance['hide_empty'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>">
						<?php _e( 'Hide empty terms?', 'sgr-ctmw' ); ?>
					</label>
				</p>

				<p>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'show_tax_title' ); ?>" name="<?php echo $this->get_field_name( 'show_tax_title' ); ?>" value="true" <?php checked( 'true', $instance['show_tax_title'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'show_tax_title' ); ?>">
						<?php _e( 'Show Taxonomy Title?', 'sgr-ctmw' ); ?>
					</label>
				</p>

				<p>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'show_hierarchical' ); ?>" name="<?php echo $this->get_field_name( 'show_hierarchical' ); ?>" value="true" <?php checked( 'true', $instance['show_hierarchical'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'show_hierachical' ); ?>">
						<?php _e( 'Show Terms as hierarchy?', 'sgr-ctmw' ); ?>
					</label>
				</p>
				
			</div><!-- end .custom-taxonomies-menu-column-inner -->
			
			<div class="custom-taxonomies-menu-column-inner custom-taxonomies-menu-column-inner-bottom">
				
				<h4><?php _e( 'Choose how new Terms are handled', 'sgr-ctmw' ); ?></h4>
				
				<p>
					<input type="radio" name="<?php echo $this->get_field_name( 'terms_handling' ); ?>" id="<?php echo $this->get_field_id( 'terms_handling' ); ?>-auto" value="auto" <?php checked('auto', $instance['terms_handling'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'terms_handling' ); ?>-auto"><?php _e('Auto (default)', 'sgr-ctmw' ); ?></label>
				</p>
				
				<p>
					<input type="radio" name="<?php echo $this->get_field_name( 'terms_handling' ); ?>" id="<?php echo $this->get_field_id( 'terms_handling' ); ?>-manual" value="manual" <?php checked('manual', $instance['terms_handling'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'terms_handling' ); ?>-manual"><?php _e('Manual', 'sgr-ctmw' ); ?></label>
				</p>
				
				<p>
					<input type="radio" name="<?php echo $this->get_field_name( 'terms_handling' ); ?>" id="<?php echo $this->get_field_id( 'terms_handling' ); ?>-tl-child" value="tl-child" <?php checked('tl-child', $instance['terms_handling'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'terms_handling' ); ?>-tl-child"><?php _e('Auto top level / smart child', 'sgr-ctmw' ); ?></label>
				</p>
				
				<p>
					<input type="radio" name="<?php echo $this->get_field_name( 'terms_handling' ); ?>" id="<?php echo $this->get_field_id( 'terms_handling' ); ?>-ntl-child" value="ntl-child" <?php checked('ntl-child', $instance['terms_handling'] ); ?> />
					<label for="<?php echo $this->get_field_id( 'terms_handling' ); ?>-ntl-child"><?php _e('Manual top level / smart child', 'sgr-ctmw' ); ?></label>
				</p>
				
				<p><?php printf( '<a href="%s" target="_blank">%s</a>', SGR_CTMW_HOME . 'faq/', __('Learn more') ); ?></p>
			
			</div><!-- end .custom-taxonomies-menu-column-inner -->
		
			<div class="custom-taxonomies-menu-column-inner custom-taxonomies-menu-column-inner-bottom">
			
				<h4><?php _e( 'About the checklists', 'sgr-ctmw' ); ?></h4>
				<p><?php _e( 'The checklists only include custom taxonomies whose register_taxonomy() "public" $arg is set to true. Note that if a taxonomy does not have any terms, it will not be displayed in the checklist.', 'sgr-ctmw' ); ?></p>
				
			</div><!-- end .custom-taxonomies-menu-column-inner -->
		
		</div><!-- end .custom-taxonomies-menu-column -->
		
		<div class="custom-taxonomies-menu-column custom-taxonomies-menu-column-right">
		
			<div class="custom-taxonomies-menu-column-inner">
		
				<h4><?php _e( 'Select taxonomies and terms', 'sgr-ctmw' ); ?></h4>

				<p><?php _e( 'Use the checklist(s) below to choose which custom taxonomies and terms you want to include in your menu. To hide a taxonomy and all its terms, uncheck the taxonomy name.', 'sgr-ctmw' ); ?></p>
		
				<?php
				// Produce a checklist of terms for each custom taxonomy
				foreach ( $custom_taxonomies as $custom_tax ) :
			
					$checkboxes = '';
				
					// Need to make sure that the taxonomy has some terms. If it doesn't, skip to the next taxonomy
					// Prevents PHP index notice when tax has no terms
					if( empty( $instance['include_' . $custom_tax->name] ) )
						continue;
					
					// Get checklist, sgr_taxonomy_checklist( $name, $custom_tax, $selected )
					$checkboxes = sgr_taxonomy_checklist( $this->get_field_name( 'include_' . $custom_tax->name ), $custom_tax, $instance['include_' . $custom_tax->name] );
					?>
			
					<div class="custom-taxonomies-menu-list">
					
						<p>
							<input type="checkbox" id="<?php echo $this->get_field_id( 'show_tax_' . $custom_tax->name ); ?>" name="<?php echo $this->get_field_name( 'show_tax_' . $custom_tax->name ); ?>" value="true" <?php checked( 'true', $instance['show_tax_'.$custom_tax->name] ); ?> />
							<label for="<?php echo $this->get_field_id( 'show_tax' . $custom_tax->name ); ?>" class="sgr-ctmw-tax-label"><?php echo $custom_tax->label; ?></label>
						</p>
				
						<ul class="custom-taxonomies-menu-checklist">
							<?php echo $checkboxes; ?>
						</ul>
					</div>
			
				<?php
				endforeach; ?>
		
			</div><!-- end .custom-taxonomies-menu-column-inner -->
		
		</div><!-- end .custom-taxonomies-menu-column -->
		
	<?php 
	}
}


add_action('widgets_init', 'register_sgr_custom_taxonomies_menu_widget');
/**
 * Register our widget
 *
 * @since 1.0
 */
function register_sgr_custom_taxonomies_menu_widget() {
	
	register_widget('SGR_Widget_Custom_Taxonomies_Menu');
}


/**
 * Creates a taxonomy checklist based on wp_terms_checklist()
 *
 * Output buffering is used so that we can run a string replace after the checklist is created
 *
 * @since 1.0
 *
 * @param $name - string
 * @param $custom_tax - array - Array object for a custom taxonomy
 * @param $selected - array - Selected terms within the taxonomy
 *
 * @return string, xhtml markup of the checklist
 */
function sgr_taxonomy_checklist($name = '', $custom_tax, $selected = array()) {
	
	$name = esc_attr( $name );

	$checkboxes = '';

	ob_start();
		
	$terms_args = array ( 'taxonomy' => $custom_tax->name, 'selected_cats' => $selected, 'checked_ontop' => false );
	
	// Note: 'hide empty' is false, therefore terms with no posts will appear in the checklist
	wp_terms_checklist( 0, $terms_args );
	
	// Replace standard checklist "name" attr with the one we need, ie 'include_' . $custom_tax->name[]
	$checkboxes .= str_replace( 'name="tax_input['.$custom_tax->name.'][]"', 'name="'.$name.'[]"', ob_get_clean() );
			
	return $checkboxes;
}