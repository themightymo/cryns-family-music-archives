<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$wp_email_template_general = get_option('wp_email_template_general');
$wp_email_template_style_header_image = get_option('wp_email_template_style_header_image', array() );
$wp_email_template_style_header_image['header_image'] = $wp_email_template_general['header_image'];
update_option('wp_email_template_style_header_image', $wp_email_template_style_header_image);