<?php

class FacetWP_Updater
{
    public $slug;
    public $version;


    function __construct() {
        $this->slug = 'facetwp';
        $this->version = FACETWP_VERSION;
        $this->license = get_option( 'facetwp_license' );

        add_action( 'init', array( $this, 'init' ) );
    }


    function init() {
        add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'http_request_args', array( $this, 'disable_sslverify' ), 10, 2 );
    }


    function disable_sslverify( $args, $url ) {
        if ( false !== strpos( $url, 'facetwp.com' ) ) {
            $args['sslverify'] = false;
        }

        return $args;
    }


    function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $request = wp_remote_post( 'https://facetwp.com/updater/', array(
            'body' => array(
                'action'    => 'version',
                'slug'      => $this->slug,
                'license'   => $this->license,
                'host'      => FWP()->helper->get_http_host(),
            )
        ) );

        if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {
            $response = unserialize( $request['body'] );

            if ( ! empty( $response ) ) {
                if ( 'success' == $response->activation['status'] ) {
                    if ( version_compare( $this->version, $response->version, '<' ) ) {
                        $transient->response['facetwp/index.php'] = (object) array(
                            'slug'          => $this->slug,
                            'new_version'   => $response->version,
                            'url'           => $response->url,
                            'package'       => $response->package,
                        );
                    }
                }

                update_option( 'facetwp_activation', json_encode( $response->activation ) );
            }
        }

        return $transient;
    }


    // wp-includes/update.php - wp_update_plugins()
    function plugins_api( $default = false, $action, $args ) {
        if ( 'plugin_information' == $action && $this->slug == $args->slug ) {
            $request = wp_remote_post( 'https://facetwp.com/updater/', array(
                'body' => array( 'action' => 'info', 'slug' => $this->slug )
            ) );

            if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {
                $response = unserialize( $request['body'] );

                // Trigger update notification
                if ( version_compare( $this->version, $response->version, '<' ) ) {
                    return $response;
                }
            }
        }

        return $default;
    }
}
