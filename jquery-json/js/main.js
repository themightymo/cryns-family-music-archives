jQuery(function($) {
    $.getJSON('http://music.cryns.dev/wp-json/posts/?type=cryns_audio_file')
        .success(function(response) {
	        
	        var html = '';
	        
	        $.each( response, function( key, value ) {
				
				html += '<li><a href="' + value.link + '">' + value.title + '</a></li>';
			
	        });
	        
	        $('#title').html('<ol>' + html + '</ol>');
	        
        });
        
        
});