<?php

/**
 * apptheme_get_list_type function.
 * 
 * @access public
 * @return void
 */
function apptheme_get_list_type() {

	if( get_theme_mod( 'list_control' ) ) {
	
		// if( 'default' === get_theme_mod( 'list_control' ) ) return false;
	
		return get_theme_mod( 'list_control', 'medialist' );
	}
	
	return 'medialist';

}

/**
 * apptheme_get_slider function.
 * 
 * @access public
 * @return void
 */
function apptheme_get_slider() {
	
	if( !class_exists('AppPresser_Swipers')  ) return;
		
	if( get_theme_mod( 'slider_control') != '' ) {
		echo do_shortcode('[swiper]');
	}
	
}


/**
 * apptheme_excerpt_length function.
 * 
 * @access public
 * @param mixed $length
 * @return void
 */
function apptheme_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'apptheme_excerpt_length', 999 );




/**
 * apptheme_custom_colors function.
 * 
 * @access public
 * @param mixed $colors
 * @return void
 */
function apptheme_custom_colors( $colors ) {
	
	$colors['toolbar_color'] = array(
		'default' => '#999999',
		'label'   => __( 'Toolbar Color', 'apptheme' ),
		'sprintf' => '.site-header, .site-footer, header.toolbar { background-color: **color**; }'
	);

	$colors['list_bg'] = array(
		'default' => '#FFFFFF',
		'label'   => __( 'List Background', 'apptheme' ),
		'sprintf' => '.medialist ul, .list ul, .cardlist .post { background-color: **color**; }'
	);
	
	return $colors;
	
}
add_filter( 'apptheme_customizer_color_filter', 'apptheme_custom_colors' );


/**
 * apptheme_add_customizer_controls function.
 * 
 * @access public
 * @param mixed $wp_customize
 * @return void
 */
function apptheme_add_customizer_controls( $wp_customize ) {

	/**
	 * Add Section
	 */

	$wp_customize->add_section( 'static_mobile_front_page', array(
			'title' => __('Mobile Front Page', 'apptheme'),
			'priority' => 10,
			'description' => __('', 'apptheme'),
			//'active_callback' => 'is_front_page'
		) );

	// $wp_customize->add_section( 'post_lists' , array(
	//     'title'      => __('Post Lists','mytheme'),
	//     'priority'   => 200,
	// ) );

	$wp_customize->add_setting( 'list_control' );
	$wp_customize->add_control( 'homepage_list_control', array(
		'type'     => 'select',
		'label'    => __( 'List Style', 'apptheme' ),
		'section'  => 'static_mobile_front_page',
		'description'    => 'Choose a different list style for your app.',
		'priority' => 123,
		'settings' => 'list_control',
        'choices' => array(
            'medialist' => 'Thumbnail list',
            'list' => 'No thumbnails',
            'cardlist' => 'Card List',
        ),
	) );

	if( class_exists('AppPresser_Swipers')  ) {
	
		$wp_customize->add_setting( 'slider_control' );
		$wp_customize->add_control( 'homepage_slider_control', array(
			'type'     => 'checkbox',
			'label'    => __( 'Add slider to homepage?', 'apptheme' ),
			'section'  => 'static_mobile_front_page',
			'priority' => 124,
			'settings' => 'slider_control',
		) );

	}

}
add_action( 'apptheme_add_customizer_control', 'apptheme_add_customizer_controls' );