jQuery(function($) {
    $.getJSON('http://music.cryns.dev/wp-json/posts/?type=cryns_audio_file')
        .success(function(response) {
	        
	        //This is for the html demo
		    var html = '';
	        
	        $.each( response, function( key, value ) {
				
				html += '<li><a href="' + value.link + '">' + value.title + '</a></li>';
			
	        });
	        
	        
	        $('.title').html('<ol>' + html + '</ol>');
	    
			
			
			
			/*$('#single-audio-content a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
				$('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
			});*/
			
			$(document).on('click','a', function () {
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
				$('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
			});

						
			$('.entry-content a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
				$('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
			});
			
			
			
			$('.entry-title a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
				$('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
			});
			
			
			/*$('a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
				$('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
			});*/
			
			

        });
        
        
	    $(document).on('facetwp-loaded', function() {
	        // Scroll to the top of the page after the page is refreshed
	        $('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
	        /*
	        $('.entry-content a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});
			
			$('.entry-title a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});
			*/
			$('a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});
			
	     });
	     
	     $(document).on('facetwp-refresh', function() {
		    $('html, body').animate({ scrollTop: $('#single-audio-content').offset().top }, 500);
	        /*
	        $('.entry-content a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});
			
			$('.entry-title a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});
			*/
			
			$('a').click(function(e){
				e.preventDefault();
				var link = $(this).attr("href");
				$('#single-audio-content').load(link);
			});

	     });
        
});