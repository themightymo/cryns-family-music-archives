jQuery(function($) {
    $.getJSON('http://music.cryns.dev/wp-json/posts/?type=cryns_audio_file')
        .success(function(response) {
	        
	        //This is for the html demo
		    var html = '';
	        $.each( response, function( key, value ) {
				html += '<li><a href="' + value.link + '">' + value.title + '</a></li>';
	        });
	        $('.title').html('<ol>' + html + '</ol>');
	    
			// Make all links load in the #single-audio-content div via AJAX.
			$(document).on('click','a', function () {
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
				$('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
			});

        });
        
        // Make sure the AJAX stuff works in the FacetWP outpus after using FacetWP
	    $(document).on('facetwp-loaded', function() {
	        // Scroll to the top of the page after the page is refreshed
	        $('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
	       
			$('a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});
			
	     });
	     
	     // Make sure the AJAX stuff works in the FacetWP outpus after using FacetWP
	     $(document).on('facetwp-refresh', function() {
		    $('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
			
			$('a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});

	     });
        
});