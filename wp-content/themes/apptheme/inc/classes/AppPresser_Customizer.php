<?php
/**
 * AppPresser Theme Theme Customizer
 *
 * @package AppPresser Theme
 * @since   0.0.1
 */

class AppPresser_Customizer {

	public $colors = array();

	/**
	 * AppPresser_Customizer hooks
	 * @since 1.0.6
	 */
	public function hooks() {

		return array(
			array( 'customize_register', 'register', 20  ),
			// make Theme Customizer preview reload changes asynchronously.
			array( 'customize_preview_init', 'preview_js' ),
			// Now that the controls are set, add code to wp_head
			array( 'wp_head', 'customizer_css', 210 ), // run action late
		);
	}
	

	/**
	 * Add settings/controls to the Theme Customizer.
	 * @since 0.0.1
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function register( $wp_customize ) {
	
		/**
		 * Add Settings
		 */

		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
		$wp_customize->add_setting( 'appp_logo' ); // Add setting for logo uploader
		$wp_customize->add_setting( 'top_menu1_icon' ); // Top Menu Icon
		$wp_customize->add_setting( 'top_menu2_icon' ); // Top Menu Icon
		// $wp_customize->add_setting( 'panel_menu_icon' ); // Top Menu Icon

		/**
		 * Custom Controls
		 */

		// Add control for logo uploader (actual uploader)
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'appp_logo', array(
			'label'    => __( 'Upload Logo (replaces text)', 'apptheme' ),
			'section'  => 'title_tagline',
			'settings' => 'appp_logo',
		) ) );

		// Add text field for top menu icon
		$wp_customize->add_control( 'top_menu1_icon', array(
			'type'     => 'text',
			'label'    => __( 'Top Menu 1 Icon', 'apptheme' ),
			'section'  => 'nav',
			'priority' => 10,
			'settings' => 'top_menu1_icon',
		) );

		// Add text field for top menu icon
		$wp_customize->add_control( 'top_menu2_icon', array(
			'type'     => 'text',
			'label'    => __( 'Top Menu 2 Icon', 'apptheme' ),
			'section'  => 'nav',
			'priority' => 11,
			'settings' => 'top_menu2_icon',
		) );
		
		
		do_action( 'apptheme_add_customizer_control', $wp_customize );

		// // Add text field for top menu icon
		// $wp_customize->add_control( 'panel_menu_icon', array(
		// 	'type'     => 'text',
		// 	'label'    => __( 'Panel Menu Icon', 'apptheme' ),
		// 	'section'  => 'nav',
		// 	'priority' => 12,
		// 	'settings' => 'panel_menu_icon',
		// ) );

		/**
		 * Color customizations
		 */

		foreach ( $this->colors() as $color_mod => $opts ) {
			$this->migrate_to_theme_mod( $color_mod );
			// Settings
			$wp_customize->add_setting( $color_mod, array(
				'default' => $opts['default'],
				'capability' => 'edit_theme_options',
			) );
			// Controls
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					$color_mod,
					array(
						'label' => $opts['label'],
						'section' => 'colors',
						'settings' => $color_mod,
					)
				)
			);
		} // endforeach

	}

	/**
	 * Move theme colors out of options (the old way) and into theme mods
	 * @since  1.0.3
	 * @param  array  $color Color option/mod
	 */
	public function migrate_to_theme_mod( $color_mod ) {
		if ( ! is_admin() || ! isset( $color_mod ) )
			return;
		// Check if option exists
		if ( $mod = get_option( $color_mod ) ) {
			// If so, migrate the option to a theme mod
			set_theme_mod( $color_mod, $mod );
			// delete the option
			delete_option( $color_mod );
		}
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 * @since 0.0.1
	 */
	public function preview_js() {
		wp_enqueue_script( 'appp_customizer', APPP_THEME_URL .'js/customizer.js', array( 'customize-preview' ), AppPresser_Theme_Functions::VERSION, true );
	}

	/**
	 * Applies the custom CSS for the theme.
	 * @since  0.0.1
	 */
	public function customizer_css() {

		// Build our css string
		$css = '';
		foreach ( $this->colors() as $color_mod => $opts ) {
			$css .= $this->$color_mod();
		}

		// If we have any css, add it to the <head>
		if ( $css ) {
			echo "\n".'<style type="text/css" media="screen">'. "\n" . $css .'</style>'."\n";
		}
	}

	/**
	 * All our color options and associated css selectors
	 * @since  1.0.3
	 * @return array  Array of color info
	 */
	public function colors() {
		if ( ! empty( $this->colors ) )
			return $this->colors;

		$colors = array(
			'body_bg' => array(
				'default' => '#f7f5ef',
				'label'   => __( 'Body Background', 'apptheme' ),
				'sprintf' => 'body, body #page, .app-panel, .io-modal, #buddypress div.activity-comments form.ac-form { background-color: **color**; } .swiper-carousel .swiper-slide { border-color: **color**; }',
			),
			'text_color' => array(
				'default' => '#999999',
				'label'   => __( 'Text Color', 'apptheme' ),
				'sprintf' => 'body, .entry-meta, .list-group a, .list-group a:visited { color: **color**; }',
			),
			'link_color' => array(
				'default' => '#333333',
				'label' => __( 'Link Color', 'apptheme' ),
				'sprintf' => 'a,a:visited { color: **color**; }',
			),
			/* 'link_hover' => array(
				'default' => '#2d7639',
				'label' => __( 'Link Hover Color', 'apptheme' ),
				'sprintf' => '#main a:hover, #main a:focus, #main a:active { color: **color**; }',
			), */
			'button_bg' => array(
				'default' => '#42ad54',
				'label' => __( 'Button Color', 'apptheme' ),
				'sprintf' => '.btn-primary, .woocommerce .button, input[type="submit"], #buddypress input[type=submit], .woocommerce .quantity .plus, .woocommerce .quantity .minus, .woocommerce .quantity input.qty, .log-out-button a { background-color: **color** !important; }',
			),
			/* 'button_hover' => array(
				'default' => '#378f46',
				'label' => __( 'Button Hover Color', 'apptheme' ),
				'sprintf' => '.btn-primary:hover, .woocommerce .button:hover, input[type="submit"]:hover, footer .nav>li>a:hover, footer .nav>li>a:focus { background-color: **color** !important; }',
			), */
			'accent_color' => array(
				'default' => '#42ad54',
				'label' => __( 'Accent Color', 'apptheme' ),
				'sprintf' => '.site-header, .site-footer, header.toolbar, header .search-dropdown, .woocommerce-info:before, .woocommerce-message:before, .woocommerce-pagination .page-numbers>li span.current { background-color: **color**; }
				header .dropdown-menu a, .single-product-info .summary .price { color: **color**; }
				.ios7, .woocommerce-info, .woocommerce-message { border-top-color: **color**; }
				.woocommerce-pagination .page-numbers>li span.current { border-color: **color**; }',
			),
			'headings_color' => array(
				'default' => '#999999',
				'label' => __( 'Headings Color', 'apptheme' ),
				'sprintf' => '.page-title, #main h1, #main h2, #main h3, #main h4, #main h1 a, #main h2 a, #main h3 a, #main h4 a { color: **color**; }',
			),
			'top_bar_text_color' => array(
				'default' => '#ffffff',
				'label' => __( 'Header/Footer Text Color', 'apptheme' ),
				'sprintf' => 'header .site-title, header .site-title a, .site-header a, .toolbar .btn, .app-panel .nav-right-btn, header .widget_search a, .io-modal .io-modal-close, footer .nav > li > a { color: **color**; }',
			),
			'left_panel_bg' => array(
				'default' => '#333333',
				'label' => __( 'Left Panel Background', 'apptheme' ),
				'sprintf' => '.snap-drawer, .navigation-main ul ul, .navigation-main ul ul ul, .cart-items .cart-contents .amount { background-color: **color**; }',
			),
			'left_panel_text' => array(
				'default' => '#eeeeee',
				'label' => __( 'Left Panel Text', 'apptheme' ),
				'sprintf' => '.snap-drawer, .navigation-main a, .cart-items, .cart-items a, .navigation-main .nav-divider a, .shelf-top .user-name { color: **color** !important; }',
			),
		);
		
		
		$this->colors = apply_filters( 'apptheme_customizer_color_filter', $colors );

		return $this->colors;
	}

	/**
	 * Fallback method.. Takes a color_mod key and creates css for the setting.
	 * @since  1.0.3
	 * @param  string  $color_mod Name of the method called, our color theme mods
	 * @param  array   $args      Arguments passed to the method
	 * @return string             CSS selectors
	 */
	public function __call( $color_mod, $args ) {
		// If not a color mod, then stop here
		if ( ! array_key_exists( $color_mod, $this->colors() ) )
			return '';
		// Ok, see if we have a stored setting
		$mod = get_theme_mod( $color_mod );
		
		// If so, and we have a css format, create the css selector
		if ( isset( $this->colors[ $color_mod ]['sprintf'] ) && $mod ) {
			return $this->format( $this->colors[ $color_mod ]['sprintf'], $mod );
		}
	}

	/**
	 * Format the css string with the mod data
	 * @since  1.0.3
	 * @param  string  $format Format string
	 * @param  string  $data   CSS color string
	 * @return string          Formatted css selector string
	 */
	public function format( $format, $data ) {
		$formatted = str_ireplace(
			array( '**color**', "\t", '; }', ' { ', '} ' ),
			array( $data, '', ";\n}", " {\n\t", "}\n" ),
			$format
		). "\n";
		return $formatted;
	}
}


function appp_customizer_live_preview() {

	wp_enqueue_script(
		'appp-theme-customizer',
		get_template_directory_uri() . '/js/theme-customizer.js',
		array( 'jquery', 'customize-preview' ),
		'',
		true
	);

} 
add_action( 'customize_preview_init', 'appp_customizer_live_preview' );