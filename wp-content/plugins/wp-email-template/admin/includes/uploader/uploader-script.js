(function ($) {

a3Uploader = {
	removeFile: function () {
		$(document).on( 'click', '.a3_uploader_remove', function(event) { 
			$(this).hide();
			$(this).parents().parents().children( '.a3_upload').attr( 'value', '' );
			$(this).parents( '.a3_screenshot').slideUp();
			
			return false;
		});
	},
	
	mediaUpload: function () {
		jQuery.noConflict();
		
		var formfield, formID, upload_title, btnContent = true;
	
		$(document).on( 'click', 'input.a3_upload_button', function () {
			formfield = $(this).prev( 'input').attr( 'id' );
			formID = $(this).attr( 'rel' );
			upload_title =  $(this).prev( 'input').attr( 'rel' );
								   
			tb_show( upload_title, 'media-upload.php?post_id='+formID+'&amp;title=' + upload_title + '&amp;a3_uploader=yes&amp;TB_iframe=1' );
			return false;
		});
				
		window.original_send_to_editor = window.send_to_editor;
		window.send_to_editor = function(html) {
			if (formfield) {
				if ( $(html).html(html).find( 'img').length > 0 ) {
					itemurl = $(html).html(html).find( 'img').attr( 'src' );
				} else {
					var htmlBits = html.split( "'" );
					itemurl = htmlBits[1]; 
					var itemtitle = htmlBits[2];
					itemtitle = itemtitle.replace( '>', '' );
					itemtitle = itemtitle.replace( '</a>', '' );
				}
				var image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;
				var document = /(^.*\.pdf|doc|docx|ppt|pptx|odt*)/gi;
				var audio = /(^.*\.mp3|m4a|ogg|wav*)/gi;
				var video = /(^.*\.mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2*)/gi;
			  
				if (itemurl.match(image)) {
					btnContent = '<img class="a3_uploader_image" src="'+itemurl+'" alt="" /><a href="#" class="a3_uploader_remove a3-plugin-ui-delete-icon">&nbsp;</a>';
				} else {
					html = '<a href="'+itemurl+'" target="_blank" rel="a3_external">View File</a>';
					btnContent = '<div class="a3_no_image"><span class="a3_file_link">'+html+'</span><a href="#" class="a3_uploader_remove a3-plugin-ui-delete-icon">&nbsp;</a></div>';
				}
				$( '#' + formfield).val(itemurl);
				$( '#' + formfield).siblings( '.a3_screenshot').slideDown().html(btnContent);
				tb_remove();
			} else {
				window.original_send_to_editor(html);
			}
			formfield = '';
		}
	}
};
	
	$(document).ready(function () {

		a3Uploader.removeFile();
		a3Uploader.mediaUpload();
	
	});

})(jQuery);
