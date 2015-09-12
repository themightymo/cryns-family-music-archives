<?php
/**
 * This file calls the init.php file, but only
 * if the child theme hasn't called it first.
 *
 * This method allows the child theme to load
 * the framework so it can use the framework
 * components immediately.
 *
 * @package AppPresser Theme
 * @version 1.0.1
 */
require_once( dirname( __FILE__ ) . '/inc/init.php' );
// load customizer options
require_once( dirname( __FILE__ ) . '/inc/customizer.php' );