<?php 

global $AjaxyLiveSearch;
?>
<div class="ajaxy-wrap">
	<h2><?php _e('Select the search settings below and click generate shortcode.'); ?></h2>
	<div class="ajaxy-form-left">
		<h3><?php _e('Search Settings'); ?></h3>
		<div class="ajaxy-form-table">
		<input type="hidden" name="action" value="ajaxy_sf_shortcode" />
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label><?php _e('Show Categories'); ?></label></th>
					<td>
						<input type="checkbox" name="sf[style][show_category]" checked="checked" value="1"/>
						<span class="description"><?php _e('Show the categories in the search results.'); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Show Post Categories'); ?></label></th>
					<td>
						<input type="checkbox" name="sf[style][show_post_category]" checked="checked" value="1"/>
						<span class="description"><?php _e('Show post of found categories in the search results.'); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Post types'); ?></label></th>
					<td>
						<ul class="ajaxy-sf-select">
						<?php
						$post_types = get_post_types( '', 'objects' ); 
						foreach ( $post_types as $post_type ) {
							?>
							<li><input type="checkbox" name="sf[style][post_types][]" value="<?php echo $post_type->name; ?>"/><?php echo $post_type->label; ?></li>
							<?php
						}
						?>
						</ul>
						<span class="description"><?php _e('Select which post types to search, don\'t select any if you want to search all.'); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		</div>
		<h3><?php _e('Search Form box'); ?></h3>
		<div class="ajaxy-form-table">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label><?php _e('Use Right to Left styles'); ?></label></th>
					<td>
						<input type="checkbox" name="sf[style][rtl]" <?php echo  $AjaxyLiveSearch->get_style_setting('rtl_theme', 0 ) > 0 ? 'checked="checked"' : ''; ?> value="1"/>
						<span class="description"><?php _e('Check this in case you want to use rtl themes to support right to left languages like arabic.'); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Search label'); ?></label></th>
					<td>
						<input type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('search_label',  _('Search')); ?>" name="sf[style][label]" class="regular-text">
						<p class="description"><?php _e('This label appears inside the search form and will be hidden when the user clicks inside.'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Width'); ?></label></th>
					<td>
						<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('width', 180); ?>" name="sf[style][width]" class="regular-text">
						<p class="description"><?php _e('The width of the search form (width is per pixel) - the value should be integer.'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Allow expand'); ?></label></th>
					<td>
						<input type="checkbox" name="sf[style][allow_expand]" <?php echo  $AjaxyLiveSearch->get_style_setting('expand', 0 ) > 0 ? 'checked="checked"' : ''; ?>/>
						<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('expand', false); ?>" name="sf[style][expand]" class="regular-text">
						<p class="description"><?php _e('The reduced width of the search form (this will allow the form to expand its width when it gains focus).'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Delay time'); ?></label></th>
					<td>
						<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('delay', 500); ?>" name="sf[style][delay]" class="regular-text">
						<p class="description"><?php _e('The delay time before showing the results (this will allow the user to input more text before searching) -  <b>(in millisecond, i.e 5000 = 5sec)</b>.'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Border width'); ?></label></th>
					<td>
						<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('border-width' , 1); ?>" name="sf[style][b_width]" class="regular-text">
						<p class="description"><?php _e('The width of the search form border.'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Border type'); ?></label></th>
					<td>
						<select name="sf[style][b_type]">
							<option value="solid" <?php echo ($AjaxyLiveSearch->get_style_setting('border-type',  'solid') == 'solid' ? 'selected="selected"' : ""); ?>><?php _e('solid'); ?></option>
							<option value="dotted" <?php echo ($AjaxyLiveSearch->get_style_setting('border-type') == 'dotted' ? 'selected="selected"' : ""); ?>><?php _e('dotted'); ?></option>
							<option value="dashed" <?php echo ($AjaxyLiveSearch->get_style_setting('border-type') == 'dashed' ? 'selected="selected"' : ""); ?>><?php _e('dashed'); ?></option>
						</select>
						<p class="description"><?php _e('The type of the search form border.'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Border color'); ?></label></th>
					<td>
						<input style="width:52px" type="text" value="<?php echo $AjaxyLiveSearch->get_style_setting('border-color','eee'); ?>" name="sf[style][b_color]" class="regular-text">
						<p class="description"><?php _e('The color of the search form border (color value is hexa-decimal).'); ?></p>
					</td>
				</tr>
				
			</tbody>
		</table>
		</div>
		<h3><?php _e('Search Results box'); ?></h3>
		<div class="ajaxy-form-table">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label><?php _e('Width'); ?></label></th>
					<td>
						<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('results_width', 315); ?>" name="sf[style][results_width]" class="regular-text">
						<p class="description"><?php _e('The width of the results box (width is per pixel) - the value should be integer.'); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		</div>
		<h3><?php _e('More results box'); ?></h3>
		<div class="ajaxy-form-table">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label><?php _e('Search Url'); ?></label></th>
					<td>
						<input type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('search_url',  home_url().'/?s=%s'); ?>" name="sf[style][url]" class="regular-text">
						<p class="description"><?php _e('This search URL for the "See more results"'); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		</div>
		<h3><?php _e('Credits'); ?></h3>
		<div class="ajaxy-form-table">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<td colspan="2">
						<input type="checkbox" name="sf[style][credits]" <?php echo  $AjaxyLiveSearch->get_style_setting('credits', 1 ) == 1 ? 'checked="checked"' : ''; ?> value="1"/>
						<span class="description"><?php _e('Author "Powered by" link and credits.'); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		</div>
	</div>
	
	<div class="ajaxy-form-right">
		<h3><?php _e('Shortcode'); ?></h3>
		<div class="ajaxy-form-table">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<td scope="row">
							<button class="button-primary" name="sf_submit" type="submit"><?php _e('Generate shortcode'); ?></button>
							<!--<button class="button-primary" name="sf_submit" type="button" ><?php _e('Preview shortcode'); ?></button>-->
						</td>
					</tr>
					<tr valign="top">
						<td>
							<textarea id="shortcode-text" style="width:99%;min-height:150px"></textarea>
							<span class="description"><?php _e('Copy the shortcode to where you want it to appear.'); ?></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#ajaxy-form").submit(function(e)
		{
			var postData = jQuery(this).serializeArray();
			jQuery.ajax(
			{
				url : ajaxurl,
				type: "POST",
				data : postData,
				success:function(data, textStatus, jqXHR) 
				{
					jQuery('#shortcode-text').val(data);
				},
				error: function(jqXHR, textStatus, errorThrown) 
				{
					//if fails      
				}
			});
			e.preventDefault(); //STOP default action
			//e.unbind(); //unbind. to stop multiple form submit.
			return false;
		});
		jQuery( "#shortcode-text" ).dblclick(function() {
			jQuery(this).select();
		});
	});
	</script>
</div>