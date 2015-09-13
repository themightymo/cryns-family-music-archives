jQuery(document).ready(function($) {

	// Inject "ajaxify" class for links that are loaded via ajax http://techbirds.in/how-to-add-jquery-events-to-dynamically-or-ajax-loaded-classes/
	$(document).on('click', "a", function () {
		$( "a" ).addClass( "ajaxify" );
	});
	
});
