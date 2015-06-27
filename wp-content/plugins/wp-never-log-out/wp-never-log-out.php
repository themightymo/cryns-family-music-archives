<?php
/*
Plugin Name: WP Never Log Out
Plugin URI: http://www.themightymo.com
Description: Keeps you logged in for 40+ years (or until your cookie is removed from your browser).
Version: 1.0
Author: Toby Cryns
Author URI: http://www.tobycryns.com
*/


/*  Copyright 2015  Toby Cryns  (email : toby@themightymo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
    Original author: Viper007Bond via https://wordpress.stackexchange.com/questions/515/whats-the-easiest-way-to-stop-wp-from-ever-logging-me-out
    
*/

add_filter( 'auth_cookie_expiration', 'wp_never_log_out' );

function wp_never_log_out( $expirein ) {
    return 1421150815; // 40+ years in seconds
}