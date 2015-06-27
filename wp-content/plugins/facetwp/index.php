<?php
/*
Plugin Name: FacetWP
Plugin URI: https://facetwp.com/
Description: Faceted Search and Filtering for WordPress
Version: 1.9.3
Author: Matt Gibbs
Author URI: https://facetwp.com/

Copyright 2014 Matt Gibbs

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) or exit;

class FacetWP
{

    public $ajax;
    public $facet;
    public $helper;
    public $indexer;
    public $display;
    public $vendor;
    private static $instance;


    function __construct() {

        // setup variables
        define( 'FACETWP_VERSION', '1.9.3' );
        define( 'FACETWP_DIR', dirname( __FILE__ ) );
        define( 'FACETWP_URL', plugins_url( 'facetwp' ) );

        // automatic updates
        include( FACETWP_DIR . '/includes/class-updater.php' );
        $this->updater = new FacetWP_Updater( $this );

        add_action( 'init', array( $this, 'init' ) );
    }


    /**
     * Initialize the singleton
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new FacetWP;
        }
        return self::$instance;
    }


    /**
     * Prevent cloning
     */
    function __clone() {}


    /**
     * Prevent unserializing
     */
    function __wakeup() {}


    /**
     * Initialize classes and WP hooks
     */
    function init() {

        // i18n
        $this->load_textdomain();

        // classes
        foreach ( array( 'helper', 'ajax', 'facet', 'indexer', 'display', 'upgrade' ) as $f ) {
            include( FACETWP_DIR . "/includes/class-{$f}.php" );
        }

        new FacetWP_Upgrade();
        $this->helper       = new FacetWP_Helper();
        $this->facet        = new FacetWP_Facet();
        $this->indexer      = new FacetWP_Indexer();
        $this->display      = new FacetWP_Display();
        $this->ajax         = new FacetWP_Ajax();

        // integrations
        include( FACETWP_DIR . '/includes/integrations/searchwp.php' );

        // include global functions
        include( FACETWP_DIR . '/includes/functions.php' );

        // hooks
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }


    /**
     * i18n support
     */
    function load_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'fwp' );
        $mofile = WP_LANG_DIR . '/facetwp/fwp-' . $locale . '.mo';

        if ( file_exists( $mofile ) ) {
            load_textdomain( 'fwp', $mofile );
        }
        else {
            load_plugin_textdomain( 'fwp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }
    }


    /**
     * Register the FacetWP settings page
     */
    function admin_menu() {
        add_options_page( 'FacetWP', 'FacetWP', 'manage_options', 'facetwp', array( $this, 'settings_page' ) );
    }


    /**
     * Enqueue jQuery
     */
    function front_scripts() {
        wp_enqueue_script( 'jquery' );
    }


    /**
     * Enqueue admin tooltips
     */
    function admin_scripts( $hook ) {
        if ( 'settings_page_facetwp' == $hook ) {
            wp_enqueue_script( 'jquery-powertip', FACETWP_URL . '/assets/js/jquery-powertip/jquery.powertip.min.js', array( 'jquery' ), '1.2.0' );
        }
    }


    /**
     * Route to the correct edit screen
     */
    function settings_page() {
        include( FACETWP_DIR . '/templates/page-settings.php' );
    }


    /**
     * Add license renew notifications to WP's plugin listing
     */
    function plugin_row_meta( $plugin_meta, $plugin_file ) {
        if ( 'facetwp/index.php' == $plugin_file ) {
            $show_expiration = true;
            $activation = get_option( 'facetwp_activation' );
            $message = '<a class="fwp-renew" href="options-general.php?page=facetwp">' . __( 'Activate license', 'fwp' ) . '</a>';
            if ( ! empty( $activation ) ) {
                $activation = json_decode( $activation );
                if ( 'success' == $activation->status ) {
                    $expires = strtotime( $activation->expiration );
                    if ( 0 < $expires - strtotime( '+2 months' ) ) {
                        $show_expiration = false;
                    }
                    elseif ( $expires > time() ) {
                        $expires = floor( ( $expires - time() ) / 86400 ) . ' ' . __( 'days left', 'fwp' );
                    }
                    else {
                        $expires = __( 'expired', 'fwp' );
                    }
                    $message = '<a class="fwp-renew" href="https://facetwp.com/documentation/installation/#renewal" target="_blank">' . __( 'Renew license', 'fwp' ) . "</a> ($expires)";
                }
            }

            if ( $show_expiration ) {
                array_pop( $plugin_meta );
                $message .= '<style>.fwp-renew:before { color:#d54e21; content:"\f112"; font-family:dashicons; font-size:16px; margin:0 5px 0 0; vertical-align:top; }</style>';
                $plugin_meta[] = $message;
            }
        }
        return $plugin_meta;
    }
}

$facetwp = FWP();


/**
 * Allow direct access to FacetWP classes
 * For example, use FWP()->helper to access FacetWP_Helper
 */
function FWP() {
    return FacetWP::instance();
}
