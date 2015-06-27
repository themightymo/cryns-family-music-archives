<?php 

global $AjaxyLiveSearch;
$message = false;

if(isset($_POST['sf_rsubmit']) && wp_verify_nonce($_POST['_wpnonce'])){
	$styles = $_POST['sf']['style'];
	foreach($styles as $key=>$value){
		$AjaxyLiveSearch->remove_style_setting($key);
	}
	$AjaxyLiveSearch->remove_template('more');
}
elseif(isset($_POST['sf_submit']) && wp_verify_nonce($_POST['_wpnonce'])){
	$styles = $_POST['sf']['style'];
	$templates = $_POST['sf']['template'];
	$AjaxyLiveSearch->set_style_setting('search_label'	, $styles['label']); 
	$AjaxyLiveSearch->set_style_setting('input_id'	, $styles['input_id']); 
	$AjaxyLiveSearch->set_style_setting('width'			, (int)$styles['width']);
	if(isset($styles['allow_expand'])){
		$AjaxyLiveSearch->set_style_setting('expand'		, (int)$styles['expand']);
	}
	else{
		$AjaxyLiveSearch->set_style_setting('expand'		, 0);
	}
	if(isset($styles['credits'])){
		$AjaxyLiveSearch->set_style_setting('credits'		, 1);
	}
	else{
		$AjaxyLiveSearch->set_style_setting('credits'		, 0);
	}
	if(isset($styles['aspect_ratio'])){
		$AjaxyLiveSearch->set_style_setting('aspect_ratio'		, 1);
	}
	else{
		$AjaxyLiveSearch->set_style_setting('aspect_ratio'		, 0);
	}
	if(isset($styles['hook_form'])){
		$AjaxyLiveSearch->set_style_setting('hook_search_form'		, 1);
	}
	else{
		$AjaxyLiveSearch->set_style_setting('hook_search_form'		, -1);
	}if(isset($styles['rtl'])){
		$AjaxyLiveSearch->set_style_setting('rtl_theme'		, 1);
	}
	else{
		$AjaxyLiveSearch->set_style_setting('rtl_theme'		, 0);
	}
	$AjaxyLiveSearch->set_style_setting('delay'			, (int)$styles['delay']);
	$AjaxyLiveSearch->set_style_setting('border-width' 	, (int)$styles['b_width']);
	$AjaxyLiveSearch->set_style_setting('border-type'	, $styles['b_type']);
	$AjaxyLiveSearch->set_style_setting('search_url'	, $styles['url']);
	$AjaxyLiveSearch->set_style_setting('border-color'	, $styles['b_color']);
	$AjaxyLiveSearch->set_style_setting('results_width'	, (int)$styles['results_width']); 
	$AjaxyLiveSearch->set_style_setting('excerpt' 		, $styles['excerpt']);
	$AjaxyLiveSearch->set_style_setting('css'			, $styles['css']);
	//$AjaxyLiveSearch->set_style_setting('results_position'	, $styles['results_position']);
	$AjaxyLiveSearch->set_style_setting('thumb_width'	, $styles['thumb_width']);
	$AjaxyLiveSearch->set_style_setting('thumb_height'	, $styles['thumb_height']);
	$AjaxyLiveSearch->set_templates('more'	, $templates['more_results']);
	$message = "Settings saved";
}
?>
<?php if ( $message ) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div class="ajaxy-wrap">
	<h3><?php _e('Search Form box'); ?></h3>
	<div class="ajaxy-form-table">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label><?php _e('Allow Ajaxy to Hook Search'); ?></label></th>
				<td>
					<input type="checkbox" name="sf[style][hook_form]" <?php echo  $AjaxyLiveSearch->get_style_setting('hook_search_form', 1 ) > 0 ? 'checked="checked"' : ''; ?>/>
					<span class="description"><?php _e('unCheck this in case you want to use your theme search form and use the ID box.'); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Use Right to Left styles'); ?></label></th>
				<td>
					<input type="checkbox" name="sf[style][rtl]" <?php echo  $AjaxyLiveSearch->get_style_setting('rtl_theme', 0 ) > 0 ? 'checked="checked"' : ''; ?>/>
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
				<th scope="row"><label><?php _e('Input ID or class name'); ?></label></th>
				<td>
					<input type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('input_id',  ""); ?>" name="sf[style][input_id]" class="regular-text">
					<p class="description"><?php _e('keep this blank to use ajaxy search form, or else put the id of the search or the class name in the form (#ID for id (# before the id) or else (.className) ( "." before the className).'); ?></p>
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
			<?php /*
			<tr valign="top">
				<th scope="row"><label><?php _e('Position'); ?></label></th>
				<td>
					<select name="sf[style][results_position]">
						<option value="0" <?php echo ($AjaxyLiveSearch->get_style_setting('results_position', 0) == 0 ? 'selected="selected"' : ""); ?>>left</option>
						<option value="1" <?php echo ($AjaxyLiveSearch->get_style_setting('results_position', 0) == 1 ? 'selected="selected"' : ""); ?>>right</option>
					</select>
					<p class="description"><?php _e('The position of the results box (it can be displayed starting from the left or from the right)'); ?></p>
				</td>
			</tr>
			*/ ?>
			<tr valign="top">
				<th scope="row"><label><?php _e('Total words'); ?></label></th>
				<td>
					<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('excerpt' , 10); ?>" name="sf[style][excerpt]" class="regular-text">
					<p class="description"><?php _e('The post content total number of words to be shown under each result.'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Thumb size'); ?></label></th>
				<td>
					<label><?php _e('height'); ?></label>
					<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('thumb_height' , 50); ?>" name="sf[style][thumb_height]" class="regular-text">
					<label><?php _e('X width'); ?></label>
					<input style="width:40px" type="text" value="<?php echo  $AjaxyLiveSearch->get_style_setting('thumb_width' , 50); ?>" name="sf[style][thumb_width]" class="regular-text">
					<input type="checkbox" name="sf[style][aspect_ratio]" <?php echo  $AjaxyLiveSearch->get_style_setting('aspect_ratio', 0 ) > 0 ? 'checked="checked"' : ''; ?>/><label><?php _e('Maintain aspect ratio'); ?></label>
					<br class="clear" />
					<p class="description"><?php _e('The thumbnail size used in the post template it will modify {post_image_html} only, Maintaining aspect ratio is relatively slow so be aware, modifing the thumb size will need some css changes.'); ?></p>
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
			<tr valign="top">
				<td colspan="2">
					<textarea style="width:99%; height:150px" name="sf[template][more_results]" class="regular-text"><?php echo $AjaxyLiveSearch->get_templates('more', 'more'); ?></textarea>
					<br class="clear"/>
					<p class="description"><?php _e('More results text (allowed parameters ( <b>{search_value}</b> <b>{search_value_escaped}</b> <b>{search_url_escaped}</b>).'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<h3><?php printf(_('Custom styles (%s)'), '<a href="http://www.w3schools.com/css/css_syntax.asp" target="_blank" rel="nofollow">CSS</a>'); ?></h3>
	<div class="ajaxy-form-table">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<td colspan="2">
					<textarea style="width:99%; height:150px" name="sf[style][css]" class="regular-text"><?php echo $AjaxyLiveSearch->get_style_setting('css', ''); ?></textarea>
					<br class="clear"/>
					<p class="description"><?php _e('Custom styles to be added in the plugin css. add ( .screen-reader-text { display:none; } ) if you want to hide the search form title.'); ?></p>
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
					<input type="checkbox" name="sf[style][credits]" <?php echo  $AjaxyLiveSearch->get_style_setting('credits', 1 ) == 1 ? 'checked="checked"' : ''; ?>/>
					<span class="description"><?php _e('Author "Powered by" link and credits.'); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<br class="clear"/>
	<input class="button-primary" name="sf_submit" type="submit" value="Save Changes" />
	<input class="button-primary" onclick="return confirm('<?php _e('Are you sure you want to reset all your settings?'); ?>');" name="sf_rsubmit" type="submit" value="<?php _e('Reset Setting to defaults'); ?>" />
	<br class="clear"/>
	<br class="clear"/>
</div>