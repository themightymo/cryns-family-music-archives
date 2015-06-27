<?php
/**
 * Advanced form for inclusion in the administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */
 
$type = isset($_GET['type']) ? $_GET['type'] : exit();
$role = false;

$role = get_role($_GET['name']);

global $wp_roles;
$roles = $wp_roles->get_names();

$role->label = isset($roles[$role->name]) ?  $roles[$role->name] : $role->name;

global $AjaxyLiveSearch;
$message = false;
if(!empty($role)){
	if(!empty($_POST['sf_post'])){
		if(wp_verify_nonce($_REQUEST['_wpnonce'], 'sf_edit')){
			if(!empty($_POST['sf_'.$role->name])){
				$AjaxyLiveSearch->set_templates('role_'.$role->name, $_POST['sf_'.$role->name]);
			}
			if(!empty($_POST['sf_title_'.$role->name])){
				$values = array(
					'title' => $_POST['sf_title_'.$role->name], 
					'show' => (int)$_POST['sf_show_'.$role->name],
					'search_content' => $_POST['sf_search_content_'.$role->name],
					'limit' => $_POST['sf_limit_'.$role->name],
					'order' => $_POST['sf_order_'.$role->name],
					'excludes' => isset($_POST['sf_exclude_'.$role->name]) ? $_POST['sf_exclude_'.$role->name]: ''
					);
				if(!empty($_POST['sf_order_results_'.$role->name])){
					$values['order_results'] = trim($_POST['sf_order_results_'.$role->name]);
				}
				if(!empty($_POST['sf_ushow_'.$role->name])){
					$values['ushow'] = trim($_POST['sf_ushow_'.$role->name]);
				}
				$AjaxyLiveSearch->set_setting('role_'.$role->name, $values);
			}
			$message = _("Settings saved");
		}
		else{
			$message = _("Settings have been already saved");
		}
	}

	
	$setting = (array)$AjaxyLiveSearch->get_setting('role_'.$role->name, false);

	$allowed_tags = array('ID', 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'display_name', 'author_link');

	$title  = sprintf(_('Edit %s template & settings'), $role->label);
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
	<input type="hidden" name="sf_post" value="<?php echo $role->name; ?>"/>
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
									<p><select name="sf_show_<?php echo $role->name; ?>">
										<option value="1"<?php echo ($setting['show'] == 1 ? ' selected="selected"':''); ?>><?php _e('Show on search'); ?></option>
										<option value="0"<?php echo ($setting['show'] == 0 ? ' selected="selected"':''); ?>><?php _e('hide on search'); ?></option>
									</select></p>
								</div>
								<div class="misc-pub-section"><label><?php _e('Search mode:'); ?></label>
									<p><select name="sf_search_content_<?php echo $role->name; ?>">
										<option value="0"<?php echo ($setting['search_content'] == 0 ? ' selected="selected"':''); ?>><?php _e('Only title'); ?></option>
										<option value="1"<?php echo ($setting['search_content'] == 1 ? ' selected="selected"':''); ?>><?php _e('Title and content (Slow)'); ?></option>
									</select></p>
								</div>
								<?php if($type != 'category'): ?>
								<div class="misc-pub-section"><label><?php _e('Order results by:'); ?></label>
									<p><select name="sf_order_results_<?php echo $role->name; ?>">
										<option value=""<?php echo ($setting['order_results'] == '' ? ' selected="selected"':''); ?>><?php _e('None (Default)'); ?></option>
										<option value="post_title asc"<?php echo ($setting['order_results'] == 'post_title asc' ? ' selected="selected"':''); ?>><?php _e('Title - Ascending'); ?></option>
										<option value="post_title desc"<?php echo ($setting['order_results'] == 'post_title desc' ? ' selected="selected"':''); ?>><?php _e('Title - Descending'); ?></option>
										<option value="post_date asc"<?php echo ($setting['order_results'] == 'post_date asc' ? ' selected="selected"':''); ?>><?php _e('Date - Ascending'); ?></option>
										<option value="post_date desc"<?php echo ($setting['order_results'] == 'post_date desc' ? ' selected="selected"':''); ?>><?php _e('Date - Descending'); ?></option>
									</select></p>
								</div>
								<?php else: ?>
								<div class="misc-pub-section"><label><?php _e('Show "Posts under Category":'); ?></label>
									<p><select name="sf_ushow_<?php echo $role->name; ?>">
										<option value="1"<?php echo ($setting['ushow'] == 1 ? ' selected="selected"':''); ?>><?php _e('Show'); ?></option>
										<option value="0"<?php echo ($setting['ushow'] == 0 ? ' selected="selected"':''); ?>><?php _e('hide'); ?></option>
									</select></p>
								</div>
								<?php endif; ?>
								<div class="misc-pub-section " id="visibility"><label><?php _e('Order:'); ?></label> 
									<p><input type="text" style="width:50px" value="<?php echo $setting['order'] ; ?>" name="sf_order_<?php echo $role->name; ?>"/></p>
								</div>
								<div class="misc-pub-section " id="limit_results"><label><?php _e('Limit results to:'); ?></label>
									<p><input type="text" style="width:50px" value="<?php echo $setting['limit'] ; ?>" name="sf_limit_<?php echo $role->name; ?>"/></p>
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
				<h3 class="hndle"><span><?php echo sprintf(_('Excluded "%s"'), $role->label); ?></span></h3>
				<div class="inside">
					<div class="submitbox">	
						<div class="misc-pub-section" >
							<?php						
							$blogusers = get_users('role='.$role->name);
							
							if(sizeof($blogusers) > 0){
								?>
								<h4><?php echo $role->label; ?></h4>
								<div style="max-height:200px;overflow:auto">
								<ul>
								<?php
								foreach ($blogusers as $user) {
								?>
									<li><input autocomplete="off" type="checkbox" <?php echo (in_array($user->ID, (array)$excludes) ? 'checked="checked"' :''); ?> name="sf_exclude_<?php echo $role->name; ?>[]" value="<?php echo $user->ID; ?>"/> <?php echo $user->display_name; ?></li>
								<?php
								}
								?>
								</ul>
								</div>
								<?php
							}
							?>
						<hr/>
						<p class="small"><?php echo sprintf(_('Prevent selected "%s" from appearing in the search results'), $role->label); ?></p>
						
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
					<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php _e( 'Enter title here' ); ?></label>
					<input type="text" name="sf_title_<?php echo $role->name; ?>" size="30" tabindex="1" value="<?php echo (empty($setting['title']) ? $role->label : $setting['title']); ?>" id="title" autocomplete="off" />
				</div>
				<div class="inside">
				</div>
			</div>
			<div id="postdivrich" class="postarea">
				<h2><?php echo sprintf(_('"%s" Template'), $role->label); ?></h2>
				<p><?php _e( 'Changes are live, use the tags below to customize the data replaced by each template.' ); ?></p>
				<?php wp_editor( $AjaxyLiveSearch->get_templates('role_'.$role->name, $type), 'sf_'.$role->name ); ?>
				<table id="post-status-info" cellspacing="0"><tbody><tr>
					<td><b><?php _e( 'Tags:' ); ?></b>
					{<?php echo implode("}, {", $allowed_tags);?>}
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
<h3><?php _e( 'Oops it looks like this page is no longer available or have been deleted :(' ); ?></h3>
<?php
}
?>
