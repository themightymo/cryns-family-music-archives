<?php
/**
 * @package AppPresser Theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />

<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="body-container">

<section class="snap-drawer">
	<div class="shelf-top">

	<?php appp_left_panel_before(); // Hook for search, user profile, and cart items ?>

	</div><!-- .shelf-top -->

	<nav id="site-navigation" class="navigation-main" role="navigation">
		<div class="screen-reader-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'apptheme' ); ?>"><?php _e( 'Skip to content', 'apptheme' ); ?></a></div>
		<?php
		if( has_nav_menu( 'primary' ) )
			wp_nav_menu( array( 'theme_location' => 'primary' ) );
		?>
	
	<?php if( is_user_logged_in() ) : ?>
		<div class="log-out-button"><a class="btn btn-success btn-large noajax" href="<?php echo wp_logout_url( home_url() ); ?>" title="<?php _e( 'Sign Out', 'apptheme' ); ?>"><?php _e( 'Sign Out', 'apptheme' ); ?></a></div>
	<?php else: ?>
		<div class="log-out-button"><a class="btn btn-success btn-large noajax io-modal-open" href="#loginModal" title="<?php _e( 'Sign In', 'apptheme' ); ?>"><?php _e( 'Sign In', 'apptheme' ); ?></a></div>
	<?php endif; ?>

	</nav><!-- #site-navigation -->
	
</section>

<div id="page" class="hfeed site">
	<?php do_action( 'appp_before' ); ?>

	<header id="masthead" class="site-header" role="banner">

		<section class="header-inner">

			<div class="pull-left">
				<?php do_action( 'appp_header_left' ); ?>
				<a href="#" class="nav-left-btn" id="nav-left-open"><i class="fa fa-reorder fa-lg"></i></a>
			</div>

			<div class="site-title-wrap">
				<?php do_action( 'appp_page_title' ); ?>
			</div><!-- .site-title-wrap -->

			<div id="header-widget-area">

				<?php
				do_action( 'appp_header_right' );

				if ( has_nav_menu( 'top' ) ) :
				$class = ( $icon = get_theme_mod( 'top_menu1_icon' ) ) ? $icon : 'fa fa-cog';
				?>
				<nav id="top-menu1" class="top-menu pull-right" role="navigation">
				<a class="nav-right-btn dropdown-toggle" data-toggle="dropdown">
					<i class="<?php echo $class; ?> fa-lg"></i>
				</a>
					<?php
					$args = array(
						'theme_location' => 'top',
						'container_class' => 'dropdown-menu'
					);
					wp_nav_menu($args);
					?>
				</nav>

				<?php endif; ?>

				<?php if ( has_nav_menu( 'top2' ) ) :
				$class = ( $icon = get_theme_mod( 'top_menu2_icon' ) ) ? $icon : 'fa fa-globe';
				?>
				<nav id="top-menu2" class="top-menu pull-right" role="navigation">
				<a class="nav-right-btn dropdown-toggle" data-toggle="dropdown"><i class="<?php echo $class; ?> fa-lg"></i>
				</a>
					<?php
						wp_nav_menu( array(
							'theme_location' => 'top2',
							'container_class' => 'dropdown-menu'
						) );
					?>
				</nav>

			<?php endif; ?>

			</div>

		</section><!-- .header-inner -->

	</header><!-- #masthead -->

	<div id="main" <?php body_class( 'site-main' ); ?>>
