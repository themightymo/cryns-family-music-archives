jQuery(function($) {
    $.getJSON('http://music.cryns.dev/wp-json/posts/?type=cryns_audio_file')
        .success(function(response) {
	        
	        var html = '';
	        
	        $.each( response, function( key, value ) {
				
				html += '<li><a href="' + value.link + '">' + value.title + '</a></li>';
				
	            /*
		        var $content = $('#content')
	                .html(value.title);
	
	            $content.find('img').each(function() {
	                var $this = $(this);
	                $this.height($this.height() / 2);
	                $this.width($this.width() / 2);
	            });
	
	            $('#title')
	                .html(value.title)
	                .attr('href', value.link);
	            */
	            // End each    
	        });
	        $('#title').html(html);
        });
        
        
});