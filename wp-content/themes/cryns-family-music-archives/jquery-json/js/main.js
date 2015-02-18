jQuery(function($) {
	
	$( document ).ajaxComplete(function() {
	  	attachAction();
	});
	
	function attachAction () {
		
		$('a').not('.site-title a, .toby').click(function(e){
			
			e.preventDefault();
			
			//if ( $(this).class("toby") ) { return; }
			
			var link = $(this).attr("href");
			
			$(this).addClass("toby");
			
			$('#single-audio-content').load(link);
			
			//$('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500); // Scroll to the top of the page after the page is refreshed
			
		});
	}	
        
        
    // Make sure the AJAX stuff works in the FacetWP outpus after using FacetWP
    $(document).on('facetwp-loaded', function() {
        //attachAction();
     });
     
     // Make sure the AJAX stuff works in the FacetWP outpus after using FacetWP
     $(document).on('facetwp-refresh', function() {
	    //attachAction();
     });
        
});