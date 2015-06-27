(function($) {
	$(document).ready(function() {
		
		/* Apply wp color picker */
		$('.a3rev_panel_container .a3rev-color-picker').each(function(i){
			$(this).wpColorPicker({
				change: function( event, ui ) {
					//bgImage.css('background-color', ui.color.toString());
				},
				clear: function() {
					//bgImage.css('background-color', '');
				}
			});
		});
		
		/* Apply UI slider */
		$('.a3rev_panel_container div.a3rev-ui-slide').each(function(i){

			if( $(this).attr('min') != undefined && $(this).attr('max') != undefined ) {

				$(this).slider( { 
								range: "min",
								min: parseInt($(this).attr('min')), 
								max: parseInt($(this).attr('max')), 
								value: parseInt($(this).parent('.a3rev-ui-slide-container-end').parent('.a3rev-ui-slide-container-start').next(".a3rev-ui-slide-result-container").children("input").val()),
								step: parseInt($(this).attr('inc')) ,
								slide: function( event, ui ) {
									$( this ).parent('.a3rev-ui-slide-container-end').parent('.a3rev-ui-slide-container-start').next(".a3rev-ui-slide-result-container").children("input").val(ui.value);
								}
							});

				$(this).removeAttr('min').removeAttr('max').removeAttr('inc');

			}

		});
		
		/* Apply Box Shadow */
		$('.a3rev_panel_container input.a3rev-ui-box_shadow-enable').each(function(i){
			if ( $(this).is(':checked') ) {
				$(this).parent('.forminp-box_shadow').find('.a3rev-ui-box_shadow-enable-container').css( {'visibility': 'visible', 'height' : 'auto', 'overflow' : 'inherit'} );
			} else {
				$(this).parent('.forminp-box_shadow').find('.a3rev-ui-box_shadow-enable-container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden'} );
			}
			$(this).on( "a3rev-ui-onoff_checkbox-switch", function( event, value, status ) {
				if ( status == 'true') {
					$(this).parents('.forminp-box_shadow').find('.a3rev-ui-box_shadow-enable-container').hide().css( {'visibility': 'visible', 'height' : 'auto', 'overflow' : 'inherit'} ).slideDown();
				} else {
					$(this).parents('.forminp-box_shadow').find('.a3rev-ui-box_shadow-enable-container').show().css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden'} ).slideUp();
				}
			});
		});
	
		/* Apply OnOff Checbox */
		$('.a3rev_panel_container input.a3rev-ui-onoff_checkbox').each(function(i){
			var checked_label = 'ON';
			var unchecked_label = 'OFF';
			var callback = "maincheck";
			
			if( $(this).attr('checked_label') != undefined ) checked_label = $(this).attr('checked_label');
			if( $(this).attr('unchecked_label') != undefined ) unchecked_label = $(this).attr('unchecked_label');
			if( $(this).attr('callback') != undefined ) callback = $(this).attr('callback');
			var input_name = $(this).attr('name');
			
			/* Apply for Border Corner */
			if ( $(this).prop('checked') ) {
				$(this).parents('.a3rev-ui-settings-control').find('.a3rev-ui-border-corner-value-container').css( {'display': 'block'} );
			} else {
				$(this).parents('.a3rev-ui-settings-control').find('.a3rev-ui-border-corner-value-container').css( {'display': 'none'} );
			}
			
			$(this).iphoneStyle({ 
								/*resizeContainer: false,*/
								resizeHandle: false,
								handleMargin: 10,
								handleRadius: 5,
								containerRadius: 0,
								checkedLabel: checked_label, 
								uncheckedLabel: unchecked_label,
								onChange: function(elem, value) { 
										var status = value.toString();
										/* Apply for Border Corner */
										if ( status == 'true' ) {
											elem.parents('.a3rev-ui-settings-control').find('.a3rev-ui-border-corner-value-container').slideDown();
										} else {
											elem.parents('.a3rev-ui-settings-control').find('.a3rev-ui-border-corner-value-container').slideUp();
										}
										
										$('input[name="' + input_name + '"]').trigger("a3rev-ui-onoff_checkbox-switch", [elem.val(), status]);
									},
								onEnd: function(elem, value) { 
										var status = value.toString();
										
										$('input[name="' + input_name + '"]').trigger("a3rev-ui-onoff_checkbox-switch-end", [elem.val(), status]);
									}
								});
		});
		
		/* Apply OnOff Radio */
		$('.a3rev_panel_container input.a3rev-ui-onoff_radio').each(function(i){
			var checked_label = 'ON';
			var unchecked_label = 'OFF';
			
			if( $(this).attr('checked_label') != undefined ) checked_label = $(this).attr('checked_label');
			if( $(this).attr('unchecked_label') != undefined ) unchecked_label = $(this).attr('unchecked_label');
			var input_name = $(this).attr('name');
			var current_item = $(this);
			
			$(this).iphoneStyle({ 
								/*resizeContainer: false,*/
								resizeHandle: false,
								handleMargin: 10,
								handleRadius: 5,
								containerRadius: 0,
								checkedLabel: checked_label, 
								uncheckedLabel: unchecked_label,
								onChange: function(elem, value) { 
										var status = value.toString();
										if ( status == 'true') {
											$('input[name="' + input_name + '"]').not(current_item).removeAttr('checked').removeAttr('checkbox-disabled').iphoneStyle("refresh");
										}
										$('input[name="' + input_name + '"]').trigger("a3rev-ui-onoff_radio-switch", [elem.val(), status]);
									},
								onEnd: function(elem, value) { 
										var status = value.toString();
										if ( status == 'true') {
											$('input[name="' + input_name + '"]').not(current_item).removeAttr('checkbox-disabled');
											$(current_item).attr('checkbox-disabled', 'true');
										}
										$('input[name="' + input_name + '"]').trigger("a3rev-ui-onoff_radio-switch-end", [elem.val(), status]);
									}
								});
		});
		
		/* Apply for normal checkbox */
		$('.a3rev_panel_container .hide_options_if_checked').each(function(){
	
			$(this).find('input:eq(0)').change(function() {
	
				if ($(this).is(':checked')) {
					$(this).closest('fieldset, tr').nextUntil( '.hide_options_if_checked, .show_options_if_checked', '.hidden_option').hide();
				} else {
					$(this).closest('fieldset, tr').nextUntil( '.hide_options_if_checked, .show_options_if_checked', '.hidden_option').show();
				}
	
			}).change();
	
		});
		$('.a3rev_panel_container .show_options_if_checked').each(function(){
	
			$(this).find('input:eq(0)').change(function() {
	
				if ($(this).is(':checked')) {
					$(this).closest('fieldset, tr').nextUntil( '.hide_options_if_checked, .show_options_if_checked', '.hidden_option').show();
				} else {
					$(this).closest('fieldset, tr').nextUntil( '.hide_options_if_checked, .show_options_if_checked', '.hidden_option').hide();
				}
	
			}).change();
	
		});
		
		/* Apply chosen script for dropdown */
		$(".a3rev_panel_container .chzn-select").chosen(); 
		$(".a3rev_panel_container .chzn-select-deselect").chosen({ allow_single_deselect:true });
		
		/* Apply help tip script */
		$(".a3rev_panel_container .help_tip").tipTip({
			"attribute" : "data-tip",
			"fadeIn" : 50,
			"fadeOut" : 50
		});
		
		/* Apply Sub tab selected script */
		$('div.a3_subsubsub_section ul.subsubsub li a:eq(0)').addClass('current');
		$('div.a3_subsubsub_section .section:gt(0)').hide();
		$('div.a3_subsubsub_section ul.subsubsub li a:gt(0)').each(function(){
			if( $(this).attr('class') == 'current') {
				$('div.a3_subsubsub_section ul.subsubsub li a').removeClass('current');
				$(this).addClass('current');
				$('div.a3_subsubsub_section .section').hide();
				$('div.a3_subsubsub_section ' + $(this).attr('href') ).show();
			}
		});
		$('div.a3_subsubsub_section ul.subsubsub li a').click(function(){
			var clicked = $(this);
			var section = clicked.closest('.a3_subsubsub_section');
			var target  = clicked.attr('href');
		
			section.find('a').removeClass('current');
		
			if ( section.find('.section:visible').size() > 0 ) {
				section.find('.section:visible').fadeOut( 100, function() {
					section.find( target ).fadeIn('fast');
				});
			} else {
				section.find( target ).fadeIn('fast');
			}
		
			clicked.addClass('current');
			$('.last_tab').val( target );
		
			return false;
		});
		
		$('.a3rev_panel_container').each( function(i){
			$(this).css( {'visibility': 'visible', 'height' : 'auto', 'overflow' : 'inherit'} );
		})
		
		$(document).trigger("a3rev-ui-script-loaded");
	});
})(jQuery);
