<?php
/**
 * The template for displaying search forms in AppPresser Theme
 *
 * @package AppPresser Theme
 */
?>
<div class="dropdown">
  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-search"></i></a>
  <div class="dropdown-menu search-dropdown pull-right" role="menu">
	<form method="get" id="searchform" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
		<label for="s" class="screen-reader-text"><?php _ex( 'Search', 'assistive text', 'apptheme' ); ?></label>
		<input type="search" class="form-control" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" id="s" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'apptheme' ); ?>" />
		<input type="submit" class="submit" id="searchsubmit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'apptheme' ); ?>" />
	</form>
  </div>
