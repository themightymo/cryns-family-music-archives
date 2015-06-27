<?php
/**
 * Advanced form for inclusion in the administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */
 
$type = isset($_GET['type']) ? $_GET['type'] : exit();

$post_type = get_post_type_object($_GET['name']);

global $AjaxyLiveSearch;
$message = false;
if(!empty($post_type)){
	if(!empty($_POST['sf_post'])){
		if(wp_verify_nonce($_REQUEST['_wpnonce'], 'sf_edit')){
			if(!empty($_POST['sf_'.$post_type->name])){
				$AjaxyLiveSearch->set_templates($post_type->name, $_POST['sf_'.$post_type->name]);
			}
			if(!empty($_POST['sf_title_'.$post_type->name])){
				$values = array(
					'title' => $_POST['sf_title_'.$post_type->name], 
					'show' => $_POST['sf_show_'.$post_type->name],
					'search_content' => $_POST['sf_search_content_'.$post_type->name],
					'limit' => $_POST['sf_limit_'.$post_type->name],
					'order' => $_POST['sf_order_'.$post_type->name],
					'excludes' => isset($_POST['sf_exclude_'.$post_type->name]) ? $_POST['sf_exclude_'.$post_type->name]: ''
					);
				if(!empty($_POST['sf_order_results_'.$post_type->name])){
					$values['order_results'] = trim($_POST['sf_order_results_'.$post_type->name]);
				}
				if(!empty($_POST['sf_ushow_'.$post_type->name])){
					$values['ushow'] = trim($_POST['sf_ushow_'.$post_type->name]);
				}
				$AjaxyLiveSearch->set_setting($post_type->name, $values);
			}
			$message = _("Settings saved");
		}
		else{
			$message = _("Settings have been already saved");
		}
	}
	$setting = (array)$AjaxyLiveSearch->get_setting($post_type->name);

	$allowed_tags = array('id', 'post_title', 'post_author', 'post_date', 'post_date_formatted', 'post_content', 'post_excerpt', 'post_image', 'post_image_html', 'post_link', 'custom_field_(YOUR_CUSTOM_FIELD_NAME)');

	$title  = sprintf(_('Edit %s template & settings'), $post_type->label);
	$notice = '';
	?>

	<div class="wrap">
	<?php screen_icon('post'); ?>
		<h2><?php echo esc_html( $title ); ?></h2>
	<?php if ( $notice ) : ?>
		<div id="notice" class="error"><p><?php echo $notice ?></p></div>
	<?php endif; ?>
	<?php if ( $message ) : ?>
		<div id="message" class="updated"><p><?php echo $message; ?></p></div>
	<?php endif; ?>
	<form name="post" action="" method="post" id="post">
	<?php wp_nonce_field('sf_edit'); ?>
	<input type="hidden" name="sf_post" value="<?php echo $post_type->name; ?>"/>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
	<div id="side-info-column" class="inner-sidebar">
		<div id="side-sortables" class="meta-box-sortables ui-sortable">
			<div id="submitdiv" class="postbox ">
				<div class="handlediv" title="<?php _e('Click to toggle'); ?>"><br></div>
				<h3 class="hndle"><span><?php _e('Save Settings'); ?></span></h3>
				<div class="inside">
					<div class="submitbox" id="submitpost">
						<div id="minor-publishing">
							<div id="misc-publishing-actions">
								<div class="misc-pub-section"><label><?php _e('Status:'); ?></label>
									<p><select name="sf_show_<?php echo $post_type->name; ?>">
										<option value="1"<?php echo ($setting['show'] == 1 ? ' selected="selected"':''); ?>><?php _e('Show on search'); ?></option>
										<option value="0"<?php echo ($setting['show'] == 0 ? ' selected="selected"':''); ?>><?php _e('hide on search'); ?></option>
									</select></p>
								</div>
								<div class="misc-pub-section"><label><?php _e('Search mode:'); ?></label>
									<p><select name="sf_search_content_<?php echo $post_type->name; ?>">
										<option value="0"<?php echo ($setting['search_content'] == 0 ? ' selected="selected"':''); ?>><?php _e('Only title'); ?></option>
										<option value="1"<?php echo ($setting['search_content'] == 1 ? ' selected="selected"':''); ?>><?php _e('Title and content (Slow)'); ?></option>
									</select></p>
								</div>
								<?php if($type != 'category'): ?>
								<div class="misc-pub-section"><label><?php _e('Order results by:'); ?></label>
									<p><select name="sf_order_results_<?php echo $post_type->name; ?>">
										<option value=""<?php echo ($setting['order_results'] == '' ? ' selected="selected"':''); ?>><?php _e('None (Default)'); ?></option>
										<option value="post_title asc"<?php echo ($setting['order_results'] == 'post_title asc' ? ' selected="selected"':''); ?>><?php _e('Title - Ascending'); ?></option>
										<option value="post_title desc"<?php echo ($setting['order_results'] == 'post_title desc' ? ' selected="selected"':''); ?>><?php _e('Title - Descending'); ?></option>
										<option value="post_date asc"<?php echo ($setting['order_results'] == 'post_date asc' ? ' selected="selected"':''); ?>><?php _e('Date - Ascending'); ?></option>
										<option value="post_date desc"<?php echo ($setting['order_results'] == 'post_date desc' ? ' selected="selected"':''); ?>><?php _e('Date - Descending'); ?></option>
									</select></p>
								</div>
								<?php else: ?>
								<div class="misc-pub-section"><label><?php _e('Show "Posts under Category":'); ?></label>
									<p><select name="sf_ushow_<?php echo $post_type->name; ?>">
										<option value="1"<?php echo ($setting['ushow'] == 1 ? ' selected="selected"':''); ?>><?php _e('Show'); ?></option>
										<option value="0"<?php echo ($setting['ushow'] == 0 ? ' selected="selected"':''); ?>><?php _e('hide'); ?></option>
									</select></p>
								</div>
								<?php endif; ?>
								<div class="misc-pub-section " id="visibility"><label><?php _e('Order:'); ?></label>
									<p><input type="text" style="width:50px" value="<?php echo $setting['order'] ; ?>" name="sf_order_<?php echo $post_type->name; ?>"/></p>
								</div>
								<div class="misc-pub-section " id="limit_results"><label><?php _e('Limit results to:'); ?></label>
									<p><input type="text" style="width:50px" value="<?php echo $setting['limit'] ; ?>" name="sf_limit_<?php echo $post_type->name; ?>"/></p>
								</div>
							</div>
							<div class="clear"></div>
						</div>
						<div id="major-publishing-actions">
							<div id="publishing-action">
								<input type="submit" name="save" id="save" class="button-primary" value="<?php _e('Save'); ?>" tabindex="5" accesskey="p">
							</div>
							<div class="clear"></div>
						</div>
					</div>

				</div>
			</div>
			<?php 
			$excludes = (isset($setting['excludes']) && sizeof($setting['excludes']) > 0 ? $setting['excludes'] : array());
			?>
			<div id="submitdiv" class="postbox ">
				<div class="handlediv" title="<?php _e('Click to toggle'); ?>"><br></div>
				<h3 class="hndle"><span><?php echo sprintf(_('Excluded "%s"'), $post_type->label); ?></span></h3>
				<div class="inside">
					<div class="submitbox">	
						<div class="misc-pub-section" >
							<p><?php echo do_shortcode('[ajaxy-selective-search label="'.sprintf(_('Search %s to exclude'),$post_type->label).' post_types="'.$post_type->name.'" name="sf_exclude_'.$post_type->name.'" show_category="0" value="{ID}"]'); ?></p>
							<div style="max-height:200px;overflow:auto">
								<?php
								$excludes[] = 0;
								$posts = get_posts( array('post_type' => $post_type->name, 'post__in' => (array)$excludes) ); 
								if(sizeof($posts) > 0) {
								?>
								<ul>
								<?php
									foreach($posts as $pst){
									?>
										<li><input autocomplete="off" type="checkbox" checked="checked" name="sf_exclude_<?php echo $post_type->name; ?>[]" value="<?php echo $pst->ID; ?>"/> <?php echo $pst->post_title; ?></li>
									<?php
									}
									?>
								</ul>
								<?php
								}else{
									echo sprintf(_('There are no "%s" excluded yet'), $post_type->label);
								}
								?>
							</div>
						<hr/>
						<p class="small"><?php echo sprintf(_('Prevent selected "%s" from appearing in the search results'), $post_type->label); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="post-body">
		<div id="post-body-content">
			<div id="titlediv">
				<div id="titlewrap">
					<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php echo __( 'Enter title here' ); ?></label>
					<input type="text" name="sf_title_<?php echo $post_type->name; ?>" size="30" tabindex="1" value="<?php echo (empty($setting['title']) ? $post_type->label : $setting['title']); ?>" id="title" autocomplete="off" />
				</div>
				<div class="inside">
				</div>
			</div>
			<div id="postdivrich" class="postarea">
				<h2><?php echo sprintf(_('"%s" Template'), $post_type->label); ?></h2>
				<p><?php _e('Changes are live, use the tags below to customize the data replaced by each template.'); ?></p>
				<?php wp_editor( $AjaxyLiveSearch->get_templates($post_type->name, $type), 'sf_'.$post_type->name ); ?>
				<table id="post-status-info" cellspacing="0"><tbody><tr>
					<td><b><?php _e('Tags:'); ?></b>
					<?php
					if($post_type->name != 'wpsc-product')
					{
						?>
						{<?php echo implode("}, {", $allowed_tags);?>}
						<?php
						if(in_array($post_type->name, AjaxyLiveSearch::$woocommerce_post_types)) {
							$wootags = array('price_html', 'add_to_cart_button', 'sale_price', 'regular_price', 'price', 'price_including_tax', 'price_excluding_tax', 'price_suffix', 'price_html', 'price_html_from_text', 'average_rating', 'rating_count', 'rating_html', 'dimensions', 'shipping_class', 'add_to_cart_text', 'single_add_to_cart_text', 'add_to_cart_url', 'title');
							?>
							<br/><b><?php _e('WooCoomerce tags:'); ?></b> {<?php echo implode("}, {", $wootags);?>}
							<?php
						}
					}
					else
					{
						?>
						{<?php echo implode("}, {", $allowed_tags);?>}, {wpsc_price}, {wpsc_shipping}, {wpsc_image}
						<?php
					}
					?>
					
					</td>
				</tr></tbody>
				</table>
			</div>
		</div>
	</div>
	<br class="clear" />
	</div>
	</form>
	</div>
	<script type="text/javascript">
	try{document.post.title.focus();}catch(e){}
	</script>
<?php }
else {
	?>
	<h3><?php _e('Oops it looks like this page is no longer available or have been deleted :('); ?></h3>
	<?php
}
?>
