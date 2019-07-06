<?php

class FacetWP_Support
{

    public $payment_id;


    function __construct() {
        if ( FWP()->helper->is_license_active() ) {
            $activation = get_option( 'facetwp_activation' );
            $activation = json_decode( $activation );
            $this->payment_id = $activation->payment_id;
        }
    }


    function get_html() {
        $disabled = empty( $this->payment_id );

        if ( $disabled ) {
            $output = '<h3>Active License Required</h3>';
            $output .= '<p>Please activate or renew your license to access support.</p>';
        }
        else {
            $output = '<iframe src="https://facetwp.com/support/create-ticket/?sysinfo=' . $this->get_sysinfo() .'"></iframe>';
        }

        return $output;
    }


    function get_sysinfo() {
        $plugins = get_plugins();
        $active_plugins = get_option( 'active_plugins', [] );
        $theme = wp_get_theme();
        $parent = $theme->parent();

        ob_start();

?>
Home URL:                   <?php echo home_url(); ?>

Payment ID:                 <?php echo empty( $this->payment_id ) ? '' : $this->payment_id; ?>

WordPress Version:          <?php echo get_bloginfo( 'version' ); ?>

Theme:                      <?php echo $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ); ?>

Parent Theme:               <?php echo empty( $parent ) ? '' : $parent->get( 'Name' ) . ' ' . $parent->get( 'Version' ); ?>


PHP Version:                <?php echo phpversion(); ?>

MySQL Version:              <?php echo $GLOBALS['wpdb']->get_var( "SELECT VERSION()" ); ?>

Web Server Info:            <?php echo $_SERVER['SERVER_SOFTWARE']; ?>


<?php
        foreach ( $plugins as $plugin_path => $plugin ) {
            if ( in_array( $plugin_path, $active_plugins ) ) {
                echo $plugin['Name'] . ' ' . $plugin['Version'] . "\n";
            }
        }

        $output = ob_get_clean();
        $output = preg_replace( "/[ ]{2,}/", ' ', trim( $output ) );
        $output = str_replace( "\n", '{n}', $output );
        $output = urlencode( $output );
        return $output;
    }
}
