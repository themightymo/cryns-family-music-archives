<?php
	
function cfma_load_scripts () {
	wp_enqueue_script(jquery);
	wp_enqueue_script(
		'custom-script',
		get_stylesheet_directory_uri() . '/custom.js',
		array( 'jquery' )
	);
}
add_action( 'wp_enqueue_scripts', 'cfma_load_scripts' );