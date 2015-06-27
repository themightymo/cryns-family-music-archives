<?php
/**
 * @package Ajaxy
 */
/*
	Plugin Name: Ajaxy Live Search
	Plugin URI: http://ajaxy.org
	Description: Transfer wordpress form into an advanced ajax search form the same as facebook live search, This version supports themes and can work with almost all themes without any modifications
	Version: 3.0.7
	Author: Ajaxy Team
	Author URI: http://www.ajaxy.org
	License: GPLv2 or later
*/



define('AJAXY_SF_VERSION', '3.0.7');
define('AJAXY_SF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('AJAXY_THEMES_DIR', dirname(__FILE__)."/themes/");
define('AJAXY_SF_NO_IMAGE', plugin_dir_url( __FILE__ ) ."themes/default/images/no-image.gif");

require_once('admin/widgets/search.php');
	
class AjaxyLiveSearch {


	public static $woocommerce_taxonomies = array('product_cat', 'product_tag', 'product_shipping_class');
	public static $woocommerce_post_types = array('product', 'shop_order', 'shop_coupon');
	
	private $noimage = '';
	
	function __construct(){
		$this->actions();
		$this->filters();
		$this->shortcodes();
	}
	function actions(){
		//ACTIONS
		if(class_exists('AJAXY_SF_WIDGET')){
			add_action( 'widgets_init', create_function( '', 'return register_widget( "AJAXY_SF_WIDGET" );' ) );
		}
		add_action( 'wp_enqueue_scripts', array(&$this, "enqueue_scripts"));
		add_action( 'admin_enqueue_scripts', array(&$this, "enqueue_scripts"));
		
		add_action( "admin_menu",array(&$this, "menu_pages"));
		add_action( 'wp_head', array(&$this, 'head'));
		add_action( 'admin_head', array(&$this, 'head'));
		add_action( 'wp_footer', array(&$this, 'footer'));
		add_action( 'admin_footer', array(&$this, 'footer'));
		
		add_action( 'wp_ajax_ajaxy_sf', array(&$this, 'get_search_results'));
		add_action( 'wp_ajax_nopriv_ajaxy_sf', array(&$this, 'get_search_results'));
		
		add_action( 'wp_ajax_ajaxy_sf_shortcode', array(&$this, 'get_shortcode'));
		
		add_action( 'admin_notices', array(&$this, 'admin_notice') );
		add_action( 'plugins_loaded', array(&$this, 'load_textdomain') );
	}


	function filters(){
		//FILTERS
		if($this->get_style_setting( 'hook_search_form', 1 ) > 0) {
			add_filter( 'get_search_form', array(&$this, 'form_shortcode'), 1);
		}
		add_filter( 'ajaxy-overview', array(&$this, 'admin_page'), 10 );
	}
	function shortcodes() {
		add_shortcode( 'ajaxy-live-search', array(&$this, 'form_shortcode') );
		add_shortcode( 'ajaxy-selective-search', array(&$this, 'selective_input_shortcode') );
	}
	function overview(){
		echo apply_filters('ajaxy-overview', 'main');
	}
	
	function load_textdomain() {
		load_plugin_textdomain( 'ajaxy-sf', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' ); 
	}

	function menu_page_exists( $menu_slug ) {
		global $menu;
		foreach ( $menu as $i => $item ) {
				if ( $menu_slug == $item[2] ) {
						return true;
				}
		}
		return false;
	}
	
	function menu_pages(){
		if(!$this->menu_page_exists('ajaxy-page')){
			add_menu_page( _n( 'Ajaxy', 'Ajaxy', 1, 'ajaxy' ), _n( 'Ajaxy', 'Ajaxy', 1 ), 'Ajaxy', 'ajaxy-page', array(&$this, 'overview'), AJAXY_SF_PLUGIN_URL.'/images/ico.png');
		}
		add_submenu_page( 'ajaxy-page', __('Live Search'), __('Live Search'), 'manage_options', 'ajaxy_sf_admin', array(&$this, 'admin_page')); 
	}
	function admin_page(){
		$message = false;
		require_once('admin/classes/class-wp-ajaxy-sf-list-table.php');
		require_once('admin/classes/class-wp-ajaxy-sf-themes-list-table.php');
		if(isset($_GET['edit'])){
			if($_GET['type'] == 'taxonomy'){
				include_once('admin/admin-edit-taxonomy-form.php');
				return true;
			}elseif($_GET['type'] == 'role'){
				include_once('admin/admin-edit-role-form.php');
				return true;
			}else{
				include_once('admin/admin-edit-post-form.php');
				return true;
			}
			
		}
		$tab = (!empty($_GET['tab']) ? trim($_GET['tab']) : false);
		$type = (!empty($_GET['type']) ? trim($_GET['type']) : false);
		
		//form data
		switch($tab) {
			case 'woocommerce':
			case 'taxonomy':
			case 'author':
			case 'post_type':
			case 'templates':	
				$public = ($tab == 'author' ? false : true);
				if(isset($_POST['action'])){
					$action = trim($_POST['action']);
					$ids = (isset($_POST['template_id']) ? (array)$_POST['template_id'] : false);
					if($action == 'hide' && $ids){
						global $AjaxyLiveSearch;
						$k = 0;
						foreach($ids as $id){
							$setting = (array)$AjaxyLiveSearch->get_setting($id, $public);
							$setting['show'] = 0;
							$AjaxyLiveSearch->set_setting($id, $setting);
							$k ++;
						}
						$message = sprintf(_('%s templates hidden'), $k);
					}
					elseif($action == 'show' && $ids){
						global $AjaxyLiveSearch;
						$k = 0;
						foreach($ids as $id){
							$setting = (array)$AjaxyLiveSearch->get_setting($id, $public);
							$setting['show'] = 1;
							$AjaxyLiveSearch->set_setting($id, $setting);
							$k ++;
						}
						$message = sprintf(_('%s templates shown'), $k);
					}
				}
				elseif(isset($_GET['show']) && isset($_GET['name'])){
					global $AjaxyLiveSearch;
					if($tab == 'author'){
						$setting = (array)$AjaxyLiveSearch->get_setting('role_'.$_GET['name'], $public);
						$setting['show'] = (int)$_GET['show'];
						$AjaxyLiveSearch->set_setting('role_'.$_GET['name'], $setting);
					}else{
						$setting = (array)$AjaxyLiveSearch->get_setting($_GET['name'], $public);
						$setting['show'] = (int)$_GET['show'];
						$AjaxyLiveSearch->set_setting($_GET['name'], $setting);
					}
					$message = _('Template modified');
				}
				break;
			case 'themes':
				if(isset($_GET['theme']) && isset($_GET['apply'])){
					global $AjaxyLiveSearch;
					$AjaxyLiveSearch->set_style_setting('theme', $_GET['theme']);
					$message = $_GET['theme'].' theme applied';
				}
				break;
			default:
				
		}

		?>
		<style type="text/css">
		.column-order, .column-limit_results, .column-show_on_search
		{
			text-align: center !important;
			width: 75px;
		}
		</style>
		<div class="wrap">
			<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
			<h2><?php _e('Ajaxy Live Search'); ?></h2>
			<ul class="subsubsub">
				<li class="active"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false); ?>" class="<?php echo (!$tab ? 'current' : ''); ?>"><?php _e('General settings'); ?><span class="count"></span></a> |</li>
				<li class="active"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=post_type'; ?>" class="<?php echo ($tab == 'post_type' ? 'current' : ''); ?>"><?php _e('Post type Search'); ?><span class="count"></span></a> |</li>
				<li class="active"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=taxonomy'; ?>" class="<?php echo ($tab == 'taxonomy' ? 'current' : ''); ?>"><?php _e('Taxonomy Search'); ?><span class="count"></span></a> |</li>
				<li class="active"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=author'; ?>" class="<?php echo ($tab == 'author' ? 'current' : ''); ?>"><?php _e('Author Search'); ?><span class="count"></span></a> |</li>

				<li class="active ajaxy-sf-new"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=woocommerce'; ?>" class="<?php echo ($tab == 'woocommerce' ? 'current' : ''); ?>"><?php _e('WooCommerce'); ?><span class="count-new">New *</span></a> |</li>
				<!--<li class="active ajaxy-sf-new pro"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=selective'; ?>" class="<?php echo ($tab == 'selective' ? 'current' : ''); ?>"><?php _e('Selective Search'); ?><span class="count-new">New *</span></a> |</li>-->
				
				<li class="active"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=themes'; ?>" class="<?php echo ($tab == 'themes' ? 'current' : ''); ?>"><?php _e('Themes'); ?><span class="count"></span></a> |</li>
				<li class="active"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=shortcode'; ?>" class="<?php echo ($tab == 'shortcode' ? 'current' : ''); ?>"><?php _e('Shortcodes'); ?><span class="count"></span></a> |</li>
				<li class="active"><a href="<?php echo menu_page_url('ajaxy_sf_admin', false).'&tab=preview'; ?>" class="<?php echo ($tab == 'preview' ? 'current' : ''); ?>"><?php _e('Preview'); ?><span class="count"></span></a></li>
			</ul>
			<hr style="clear:both; display:block"/>
			<div id="message-bottom" class="updated">
				<table>
					<tr>
						<td>
						<p>
							<?php printf(__('please donate some dollars for this project development and themes to be created, we are trying to make this project better, if you think it is worth it then u should support it.
							contact me at %s for support and development, please include your paypal id or donation id in your message.'), '<a href="mailto:icu090@gmail.com">icu090@gmail.com</a>');?>
						</p>
						</td>
						<td>
						<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QDDZQHHCPUDDG"><img class="aligncenter size-full wp-image-180" title="btn_donateCC_LG" alt="" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" width="147" height="47"></a>
						</td>
					</tr>
				</table>
			</div>
			<form id="ajaxy-form" action="" method="post">
			<?php wp_nonce_field(); ?>
			<?php if($tab == 'post_type'): ?>
				<?php 
					$list_table = new WP_SF_List_Table($this->get_search_objects(true, 'post_type')); 
				?>
				<div>
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
			<?php elseif($tab == 'taxonomy'): ?>
				<?php 
					$list_table = new WP_SF_List_Table($this->get_search_objects(true, 'taxonomy')); 
				?>
				<div>
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
			<?php elseif($tab == 'author'): ?>
				<?php 
					$list_table = new WP_SF_List_Table($this->get_search_objects(true, 'author'), false, 'role_'); 
				?>
				<div>
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
			<?php elseif($tab == 'themes'): ?>
				<?php 
					$list_table = new WP_SF_THEMES_List_Table(); 
				?>
				<div>
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
			<?php elseif($tab == 'preview'): ?>
				<br class="clear" />
				<hr style="margin-bottom:20px"/>
				<div class="wrap">
				<?php ajaxy_search_form(); ?>
				</div>
				<hr style="margin:20px 0 10px 0"/>
				<p class="description"><?php _e('Use the form above to preview theme changes and settings, please note that the changes could vary from one theme to another, please contact the author of this plugin for more help'); ?></p>
				<hr style="margin:10px 0"/>
			<?php elseif($tab == 'selective'): ?>
			<?php elseif($tab == 'woocommerce'): 
				$list_table = new WP_SF_List_Table($this->get_search_objects(true, 'taxonomy', array(), self::$woocommerce_taxonomies));
			?>
				<h3><?php _e('WooCommerce Taxonomies'); ?></h3>
				<div class="ajaxy-form-table ajaxy-form-nowrap">
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
				<h3><?php _e('WooCommerce Post Types'); ?></h3>
			<?php
				$list_table = new WP_SF_List_Table($this->get_search_objects(true, 'post_type', self::$woocommerce_post_types, array()));
			?>
				<div class="ajaxy-form-table ajaxy-form-nowrap">
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
			<?php elseif($tab == 'author'): 
				$list_table = new WP_SF_List_Table($this->get_search_objects(false, 'author'), true, 'role_');
			?>
				<h3><?php _e('WooCommerce Taxonomies'); ?></h3>
				<div class="ajaxy-form-table ajaxy-form-nowrap">
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
				<h3><?php _e('WooCommerce Post Types'); ?></h3>
			<?php
				$list_table = new WP_SF_List_Table($this->get_search_objects(true, 'post_type', self::$woocommerce_post_types, array()));
			?>
				<div class="ajaxy-form-table ajaxy-form-nowrap">
					<?php if ( $message ) : ?>
					<div id="message" class="updated"><p><?php echo $message; ?></p></div>
					<?php endif; ?>
					<?php $list_table->display(); ?>
				</div>
			<?php elseif($tab == 'shortcode'): 
				include_once('admin/admin-shortcodes.php');
			else:
				include_once('admin/admin-settings.php');
			 endif; ?>
			 </form>
			 
		</div>
		<?php
	}
	
	function get_image_from_content($content, $width_max, $height_max){
		//return false;
		$theImageSrc = false;
		preg_match_all ('/<img[^>]+>/i', $content, $matches);
		$imageCount = count ($matches);
		if ($imageCount >= 1) {
			if (isset ($matches[0][0])) {
				preg_match_all('/src=("[^"]*")/i', $matches[0][0], $src);
				if (isset ($src[1][0])) {
					$theImageSrc = str_replace('"', '', $src[1][0]);
				}
			}
		}
		if($this->get_style_setting('aspect_ratio', 0 ) > 0){
			set_time_limit(0);
			try{
				set_time_limit(1);
				list($width, $height, $type, $attr) = @getimagesize( $theImageSrc );
				if($width > 0 && $height > 0){
					if($width < $width_max && $height < $height_max){
						return array('src' => $theImageSrc, 'width' => $width, 'height' => $height);	
					}
					elseif($width > $width_max && $height > $height_max){
						$percent_width = $width_max * 100/$width;
						$percent_height = $height_max * 100/$height;
						$percent = ($percent_height < $percent_width ? $percent_height : $percent_width);
						return array('src' => $theImageSrc, 'width' => intval($width * $percent / 100), 'height' => intval($height * $percent / 100));	
					}
					elseif($width < $width_max && $height > $height_max){
						$percent = $height * 100/$height_max;
						return array('src' => $theImageSrc, 'width' => intval($width * $percent / 100), 'height' => intval($height * $percent / 100));		
					}
					else{
						$percent = $width * 100/$width_max;
						return array('src' => $theImageSrc, 'width' => intval($width * $percent / 100), 'height' => intval($height * $percent / 100));	
					}
				}
			}
			catch(Exception $e){
				set_time_limit(60);
				return array('src' => $theImageSrc, 'width' => $this->get_style_setting('thumb_width', 50 ) , 'height' => $this->get_style_setting('thumb_height', 50 ) );
			}
		}
		else{
			return array('src' => $theImageSrc, 'width' => $this->get_style_setting('thumb_width', 50 ) , 'height' => $this->get_style_setting('thumb_height', 50 ) );	
		}
		return false;
	}
	function get_post_types()
	{
		$post_types = get_post_types(array('_builtin' => false),'objects');
		$post_types['post'] = get_post_type_object('post');
		$post_types['page'] = get_post_type_object('page');
		unset($post_types['wpsc-product-file']);
		return $post_types;
	}
	function get_excerpt_count()
	{
		return $this->get_style_setting('excerpt', 10);
	}
	function get_taxonomies() {
		$args = array(
			'public'   => true,
			'_builtin' => false
		); 
		$output = 'objects'; // or objects
		$operator = 'or'; // 'and' or 'or'
		$taxonomies = get_taxonomies( $args, $output, $operator ); 
		if ( $taxonomies ) {
			return $taxonomies;
		}
		return null;
	}
	function get_search_objects($all = false, $objects = false, $specific_post_types = array(), $specific_taxonomies = array(), $specific_roles = array())
	{
		$search = array();
		$scat = (array)$this->get_setting('category');
		$arg_category_show = isset($_POST['show_category']) ? $_POST['show_category'] : 1;
		
		$search_taxonomies = false;
		
		if($scat['show'] == 1 && $arg_category_show == 1){
			$search_taxonomies = true;
		}
		$arg_post_category_show = isset($_POST['show_post_category']) ? $_POST['show_post_category'] : 1;
		
		$show_post_category = false;
		
		if($scat['ushow'] == 1 && $arg_post_category_show == 1){
			$show_post_category = true;
		}
		
		$arg_authors_show = isset($_POST['show_authors']) ? $_POST['show_authors'] : 1;
		
		$show_authors = false;
		
		if($show_authors == 1){
			$show_authors = true;
		}
		if(!$objects || $objects == 'post_type') {
			// get all post types that are ready for search
			$post_types = $this->get_post_types();
			foreach($post_types as $post_type)
			{		
				if(sizeof($specific_post_types) == 0) {	
					$setting = $this->get_setting($post_type->name);
					if($setting -> show == 1 || $all){
						$search[] = array(
							'order' => $setting->order, 
							'name' => $post_type->name, 
							'label' => 	(empty($setting->title) ? $post_type->label : $setting->title), 
							'type' => 	'post_type'
						);
					}
				}
				elseif(in_array($post_type->name, $specific_post_types)) {
					$setting = $this->get_setting($post_type->name);
					$search[] = array(
							'order' => $setting->order, 
							'name' => $post_type->name, 
							'label' => 	(empty($setting->title) ? $post_type->label : $setting->title), 
							'type' => 	'post_type'
					);
				}
			}
		}
		if((!$objects || $objects == 'taxonomy') && $search_taxonomies) {
			// override post_types from input

			$taxonomies = $this->get_taxonomies();
			foreach($taxonomies as $taxonomy)
			{		
				if(sizeof($specific_taxonomies) == 0) {	
					$setting = $this->get_setting($taxonomy->name);
					if($setting -> show == 1 || $all){
						$search[] = array(
							'order' => $setting->order, 
							'name' => $taxonomy->name, 
							'label' => 	(empty($setting->title) ? $taxonomy->label : $setting->title), 
							'type' => 	'taxonomy',
							'show_posts' => $show_post_category
						);
					}
				}
				elseif(in_array($taxonomy->name, $specific_taxonomies)) {
					$setting = $this->get_setting($taxonomy->name);
					$search[] = array(
							'order' => $setting->order, 
							'name' => $taxonomy->name, 
							'label' => 	(empty($setting->title) ? $taxonomy->label : $setting->title), 
							'type' => 	'taxonomy',
							'show_posts' => $show_post_category
						);
				}
			}
		}elseif((!$objects || $objects == 'taxonomy')) {
			// override post_types from input

			$taxonomies = $this->get_taxonomies();
			foreach($taxonomies as $taxonomy)
			{		
				if(sizeof($specific_taxonomies) == 0) {	
					$setting = $this->get_setting($taxonomy->name);
					if($setting -> show == 1 || $all){
						$search[] = array(
							'order' => $setting->order, 
							'name' => $taxonomy->name, 
							'label' => 	(empty($setting->title) ? $taxonomy->label : $setting->title), 
							'type' => 	'taxonomy',
							'show_posts' => $show_post_category
						);
					}
				}
				elseif(in_array($taxonomy->name, $specific_taxonomies)) {
					$setting = $this->get_setting($taxonomy->name);
					$search[] = array(
							'order' => $setting->order, 
							'name' => $taxonomy->name, 
							'label' => 	(empty($setting->title) ? $taxonomy->label : $setting->title), 
							'type' => 	'taxonomy',
							'show_posts' => $show_post_category
						);
				}
			}
		}
		if(!$objects || $objects == 'author') {

			global $wp_roles;
			$roles = $wp_roles->get_names();
			
			foreach($roles as $role => $label)
			{		
				if(sizeof($specific_roles) == 0) {	
					$setting = $this->get_setting('role_'.$role, false);
					if($setting -> show == 1 || $all){
						$search[] = array(
							'order' => $setting->order, 
							'name' => $role, 
							'label' => 	(empty($setting->title) ? $label : $setting->title), 
							'type' => 	'role'
						);
					}
				}
				elseif(in_array($role, $specific_roles)) {
					$setting = $this->get_setting('role_'.$role, false);
					$search[] = array(
						'order' => $setting->order, 
						'name' => $role, 
						'label' => 	(empty($setting->title) ? $label : $setting->title), 
						'type' => 	'role'
					);
				}
			}
		}
		uasort($search, array(&$this, 'sort_search_objects'));

		return $search;
	}
	function sort_search_objects($a, $b) {
		if ($a['order'] == $b['order']) {
			return 0;
		}
		return ($a['order'] < $b['order']) ? -1 : 1;
	}
	function set_templates($template, $html)
	{
		if(get_option('sf_template_'.$template) !== false)
		{
			update_option('sf_template_'.$template, stripslashes($html));
		}
		else
		{
			add_option('sf_template_'.$template, stripslashes($html));
		}
	}
	function set_setting($name, $value)
	{
		if(get_option('sf_setting_'.$name) !== false)
		{
			update_option('sf_setting_'.$name, json_encode($value));
		}
		else
		{
			add_option('sf_setting_'.$name, json_encode($value));
		}
	}
	function remove_setting($name){
		delete_option('sf_setting_'.$name);
	}
	function get_setting($name, $public = true)
	{
		$defaults = array(
						'title' => '', 
						'show' => 1,
						'ushow' => 0,
						'search_content' => 0,
						'limit' => 5,
						'order' => 0,
						'order_results' => false
						);
		if(!$public) {
			$defaults['show'] = 0;
		}
		if(get_option('sf_setting_'.$name) !== false)
		{
			$settings = json_decode(get_option('sf_setting_'.$name));
			foreach($defaults as $key => $value) {
				if(!isset($settings->{$key})){
					$settings->{$key} = $value;
				}
			}
			return $settings;
		}
		else
		{
			return (object)$defaults;
		}
	}
	function set_style_setting($name, $value)
	{
		update_option('sf_style_'.$name, $value);
	}
	function get_style_setting($name, $default = '')
	{
		if(get_option('sf_style_'.$name) !== false)
		{
			return get_option('sf_style_'.$name, $default);
		}
		else
		{
			return $default;
		}
	}
	function remove_style_setting($name)
	{
		return delete_option('sf_style_'.$name);
	}
	function remove_template($template)
	{
		delete_option('sf_template_'.$template);
	}
	function get_templates($template, $type='')
	{
		$template_post = "";
		switch($type) {
			case 'more':
				$template_post = get_option('sf_template_more');
				if(!$template_post) {
					$template_post = '<a href="{search_url_escaped}"><span class="sf_text">See more results for "{search_value}"</span><span class="sf_small">Displaying top {total} results</span></a>';
				}
				break;
			case 'taxonomy':
				$template_post = get_option('sf_template_'.$template);
				if(!$template_post) {
					$template_post = '<a href="{category_link}">{name}</a>';
				}
				break;
			case 'author':
			case 'role':
				$template_post = get_option('sf_template_'.$template);
				if(!$template_post) {
					$template_post = '<a href="{author_link}">{user_nicename}</a>';
				}
				break;
			case 'post_type':
				$template_post = get_option('sf_template_'.$template);
				if(!$template_post && in_array($template, self::$woocommerce_post_types)) {
					$template_post = '<a href="{post_link}">{post_image_html}<span class="sf_text">{post_title} - {price}</span><span class="sf_small">Posted by {post_author} on {post_date_formatted}</span></a>';
				}elseif(!$template_post){
					$template_post = '<a href="{post_link}">{post_image_html}<span class="sf_text">{post_title} </span><span class="sf_small">Posted by {post_author} on {post_date_formatted}</span></a>';
				}
				break;
			default:
				$template_post = get_option('sf_template_'.$template);
				if(!$template_post) {
					$template_post = '<a href="{post_link}">{post_image_html}<span class="sf_text">{post_title} </span><span class="sf_small">Posted by {post_author} on {post_date_formatted}</span></a>';
				}
				break;
		}
		return $template_post;
	}
	function category($name, $taxonomy = 'category', $show_category_posts = false, $limit = 5)
	{
		global $wpdb;

		$categories = array();
		$setting = (object)$this->get_setting($taxonomy);

		$excludes = "";
		$excludes_array = array();
		if(isset($setting->excludes) && sizeof($setting->excludes) > 0 && is_array($setting->excludes)){
			$excludes = " AND $wpdb->terms.term_id NOT IN (".implode(',', $setting->excludes).")";
			$excludes_array = $setting->excludes;
		}
		$results = null;
		
		$query = "
			SELECT 
				distinct($wpdb->terms.name)
				, $wpdb->terms.term_id
				, $wpdb->term_taxonomy.taxonomy 
			FROM 
				$wpdb->terms
				, $wpdb->term_taxonomy 
			WHERE 
				name LIKE '%%%s%%' 
				AND $wpdb->term_taxonomy.taxonomy = '$taxonomy' 
				AND $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id 
			$excludes 
			LIMIT 0, %d";
			
		$query = apply_filters("sf_category_query", $wpdb->prepare($query,  $name, $setting->limit), $name, $excludes_array, $setting->limit);

		$results = $wpdb->get_results($query);

		if(sizeof($results) > 0 && is_array($results) && !is_wp_error($results))
		{
			$unset_array = array('term_group', 'term_taxonomy_id', 'taxonomy', 'parent', 'count', 'cat_ID', 'cat_name', 'category_parent');
			foreach($results as $result)
			{
				$cat = get_term($result->term_id, $result->taxonomy);
				if($cat != null && !is_wp_error($cat))
				{	
					$cat_object = new stdclass();
					$category_link = get_term_link($cat);
					$cat_object->category_link = $category_link;
					
					$matches = array();
					$template = $this->get_templates( $taxonomy, 'taxonomy' );
					preg_match_all ("/\{.*?\}/", $template, $matches);
					
					foreach($matches[0] as $match){
						$match = str_replace(array('{', '}'), '', $match);
						if(isset($cat->{$match})) {
							$cat_object->{$match} = $cat->{$match};
						}
					}
					if($show_category_posts) {
						$limit = isset($setting->limit_posts) ? $setting->limit_posts : 5;
						$psts = $this->posts_by_term($cat->term_id, $taxonomy, $limit);
						if(sizeof($psts) > 0) {
							$categories[$cat->term_id] = array('name' => $cat->name,'posts' => $this->posts_by_term($cat->term_id, $limit)); 
						}
					}
					else {
						$categories[] = $cat_object; 
					}
				}
			}
		}
		return $categories;
	}	
	function author($name, $show_author_posts = false, $limit = 5)
	{
		global $wpdb;

		$authors = array();
	
		$results = null;
		
		$query = "
			SELECT 
				*
			FROM 
				$wpdb->users
			WHERE 
				ID IN (
					SELECT 	
						ID 
					FROM 
						$wpdb->usermeta 
					WHERE 
						(meta_key = 'first_name' AND meta_value LIKE '%%%s%%')
						OR (meta_key = 'last_name' AND meta_value LIKE '%%%s%%' )
						OR (meta_key = 'nickname' AND meta_value LIKE '%%%s%%' )
				)
		";	
		$query = apply_filters("sf_authors_query", $wpdb->prepare($query,  $name,  $name,  $name), $name);

		$results = $wpdb->get_results($query);
		
		if(sizeof($results) > 0 && is_array($results) && !is_wp_error($results))
		{
			foreach($results as $result)
			{
				$authors[] = new WP_User($result->ID);
			}
		}
		return $authors;
	}
	function filter_authors_by_role($authors, $role) {
		$users = array();
		$setting = (object)$this->get_setting('role_'.$role, false);

		$excludes = "";
		$excludes_array = array();
		if(isset($setting->excludes) && sizeof($setting->excludes) > 0 && is_array($setting->excludes)){
			$excludes_array = $setting->excludes;
		}
		$template = $this->get_templates( 'role_'.$role, 'author' );
		$matches = array();
		preg_match_all ("/\{.*?\}/", $template, $matches);
		if(sizeof($matches) > 0) {
			
			foreach($authors as $author) {
				if(in_array($role, $author->roles) && !in_array($author->ID,$excludes_array)) {
					$user = new stdClass();
					foreach($matches[0] as $match) {
						$match = str_replace(array('{', '}'), '', $match);
						$method = "get_".$match;
						if(method_exists ($author->data, $method)){
							$user->{$match} = call_user_func(array($author->data,$method));
						}elseif(method_exists ($author, $match)){
							$user->{$match} = call_user_func(array($author->data,$match));
						}elseif(property_exists ($author->data, $match)){
							$user->{$match} = $author->data->{$match};
						}
					}
					if(in_array('{author_link}', $matches[0])){
						$user->author_link = get_author_posts_url($author->ID);
					}
					$users[] = $user;
				}
			}
		}
		return $users;
	}
	function posts($name, $post_type='post', $term_id = false)
	{
		global $wpdb;
		$posts = array();
		$setting = (object)$this->get_setting($post_type);
		$excludes = "";
		$excludes_array = array();
		if(isset($setting->excludes) && sizeof($setting->excludes) > 0 && is_array($setting->excludes)){
			$excludes = " AND ID NOT IN (".implode(',', $setting->excludes).")";
			$excludes_array = $setting->excludes;
		}
		
		$order_results = ($setting->order_results ? " ORDER BY ".$setting->order_results : "");
		$results = array();
		
		$query = "
			SELECT 
				$wpdb->posts.ID 
			FROM 
				$wpdb->posts
			WHERE 
				(post_title LIKE '%%%s%%' ".($setting->search_content == 1 ? "or post_content LIKE '%%%s%%')":")")." 
				AND post_status='publish' 
				AND post_type='".$post_type."' 
				$excludes 
				$order_results 
			LIMIT 0, %d";
		/*
		$meta_query = "
			SELECT 
				project_id.post_id
				, height.height
				, width.width
			FROM 
				$wpdb->postmeta
			WHERE 
				meta_key = '$meta_key'
				AND meta_value LIKE '$meta_value'
			";
		*/
		$query = apply_filters("sf_posts_query", ($setting->search_content == 1 ? $wpdb->prepare($query, $name, $name, $setting->limit) :$wpdb->prepare($query, $name, $setting->limit)), $name, $post_type, $excludes_array, $setting->search_content, $order_results, $setting->limit);

		$results = $wpdb->get_results( $query );
		
		
		if(sizeof($results) > 0 && is_array($results) && !is_wp_error($results))
		{
			$template = $this->get_templates( $post_type, 'post_type' );
			$matches = array();
			preg_match_all ("/\{.*?\}/", $template, $matches);
			if(sizeof($matches) > 0) {
				foreach($results as $result)
				{
					$pst = $this->post_object($result->ID, $term_id, $matches[0]);
					if($pst){
						$posts[] = $pst; 
					}
				}
			}
		}
		return $posts;
	}
	function posts_by_term($term_id, $taxonomy, $limit = 5){
		$posts = array();
		$args = array( 
				'showposts' => $limit,
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'terms' => $term_id,
						'field' => 'term_id',
					)
				),
				'orderby' => 'date',
				'order' => 'DESC' 
			);
		$term_query = new WP_Query( $args );
		if($term_query->have_posts()) :
			$psts = apply_filters('sf_pre_term_posts', $term_query->posts);
			if(sizeof($psts) > 0) {
				foreach($psts as $p) {
					$matches = array();
					$template = $this->get_templates( $p->post_type, 'post_type' );
					preg_match_all ("/\{.*?\}/", $template, $matches);
					$posts[] = $this->post_object($p->ID, false, $matches[0]);
				}
			}
			$posts = apply_filters('sf_term_posts', $posts);
		endif;
		return $posts;
	}
	function post_object($id, $term_id = false, $matches = false) {
		$unset_array = array('post_date_gmt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_content_filtered', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'guid', 'menu_order', 'post_mime_type', 'comment_count', 'ancestors', 'filter');
		global $post;
		$date_format = get_option( 'date_format' );
		$post = get_post($id);
		if($term_id) {	
			if(!in_category($term_id, $post->ID)){
				return false;
			}
		}
		$size = array('height' => $this->get_style_setting('thumb_height' , 50), 'width' => $this->get_style_setting('thumb_weight' , 50));
		if($post != null)
		{
			$post_object = new stdclass();
			$post_link = get_permalink($post->ID);

			if(in_array('{post_image}', $matches) || in_array('{post_image_html}', $matches)) {
				$post_thumbnail_id = get_post_thumbnail_id( $post->ID);
				if( $post_thumbnail_id > 0)
				{
					$thumb = wp_get_attachment_image_src( $post_thumbnail_id, array($size['height'], $size['width']) );
					$post_object->post_image =  (trim($thumb[0]) == "" ? AJAXY_SF_NO_IMAGE : $thumb[0]);
					if(in_array('{post_image_html}', $matches)) {
						$post_object->post_image_html = '<img src="'.$post_object->post_image.'" width="'.$size['width'].'" height="'.$size['height'].'"/>';
					}
				}
				else
				{
					if($src = $this->get_image_from_content($post->post_content, $size['height'], $size['width'])){
						$post_object->post_image = $src['src'] ? $src['src'] : AJAXY_SF_NO_IMAGE;
						if(in_array('{post_image_html}', $matches)) {
							$post_object->post_image_html = '<img src="'.$post_object->post_image.'" width="'.$src['width'].'" height="'.$src['height'].'" />';
						}
					}
					else{
						$post_object->post_image = AJAXY_SF_NO_IMAGE;
						if(in_array('{post_image_html}', $matches)) {
							$post_object->post_image_html = '';
						}
					}
				}
			}
			if($post->post_type == "wpsc-product"){
				if(function_exists('wpsc_calculate_price')){
					if(in_array('{wpsc_price}', $matches)){
						$post_object->wpsc_price = wpsc_the_product_price();
					}if(in_array('{wpsc_shipping}', $matches)){
						$post_object->wpsc_shipping = strip_tags(wpsc_product_postage_and_packaging());	
					}if(in_array('{wpsc_image}', $matches)){
						$post_object->wpsc_image = wpsc_the_product_image($size['height'], $size['width']);
					}
				}
			}
			if($post->post_type == 'product' && class_exists('WC_Product_Factory')) {
				$product_factory = new WC_Product_Factory();
				global $product;
				$product = $product_factory->get_product($post);
				if($product->is_visible()) {
					foreach($matches as $match) {
						$match = str_replace(array('{', '}'), '', $match);
						if(in_array($match, array('categories', 'tags'))) {
							$method = "get_".$match;
							if(method_exists ($product, $method)){
								$term_list = call_user_func(array($product, $method), '');
								if($term_list){
									$post_object->{$match} = '<span class="sf_list sf_'.$match.'">'.$term_list.'</span>';
								}else{
									$post_object->{$match} = "";
								}
							}
						}elseif($match == 'add_to_cart_button'){
							ob_start();
							do_action( 'woocommerce_' . $product->product_type . '_add_to_cart'  );
							$post_object->{$match} = '<div class="product">'.ob_get_contents().'</div>';
							ob_end_clean();
						}else{
							$method = "get_".$match;
							if(method_exists ($product, $method)){
								$post_object->{$match} = call_user_func(array($product, $method));
							}elseif(method_exists ($product, $match)){
								$post_object->{$match} = call_user_func(array($product, $match));
							}
						}
					}
				}
				/*
				$post->sku = $product->get_sku();
				$post->sale_price = $product->get_sale_price();
				$post->regular_price = $product->get_regular_price();
				$post->price = $product->get_price();
				$post->price_including_tax = $product->get_price_including_tax();
				$post->price_excluding_tax = $product->get_price_excluding_tax();
				$post->price_suffix = $product->get_price_suffix();
				$post->price_html = $product->get_price_html();
				$post->price_html_from_text = $product->get_price_html_from_text();
				$post->average_rating = $product->get_average_rating();
				$post->rating_count = $product->get_rating_count();
				$post->rating_html = $product->get_rating_html();
				$post->dimensions = $product->get_dimensions();
				$post->shipping_class = $product->get_shipping_class();
				$post->add_to_cart_text = $product->add_to_cart_text();
				$post->single_add_to_cart_text = $product->single_add_to_cart_text();
				$post->add_to_cart_url = $product->add_to_cart_url();
				$post->title = $product->get_title();
				*/
			}
			$post_object->ID = $post->ID;
			$post_object->post_title = get_the_title($post->ID);
			
			if(in_array('{post_excerpt}', $matches)) {
				$post_object->post_excerpt = $post->post_excerpt;
			}if(in_array('{post_author}', $matches)) {
				$post_object->post_author = get_the_author_meta('display_name', $post->post_author);
			}if(in_array('{post_link}', $matches)) {
				$post_object->post_link = $post_link;
			}if(in_array('{post_content}', $matches)) {
				$post_object->post_content = $this->get_text_words(apply_filters('the_content', $post->post_content) ,(int)$this->get_excerpt_count());
			}if(in_array('{post_date_formatted}', $matches)) {
				$post_object->post_date_formatted = date($date_format,  strtotime( $post->post_date) );
			}

			
			
			foreach($matches as $match) {
				$match = str_replace(array('{', '}'), '', $match);

				if(strpos($match, 'custom_field_') !== false){
					$key =  str_replace('custom_field_', '', $match);
					$custom_field = get_post_meta($post->ID, $key, true);
					if ( is_array($custom_field) ) {
						$cf_name = 'custom_field_'.$key;
						$post_object->{$cf_name} = apply_filters('sf_post_custom_field', $custom_field[0], $key, $post);
					}else{
						$cf_name = 'custom_field_'.$key;
						$post_object->{$cf_name} = apply_filters('sf_post_custom_field', $custom_field, $key, $post);
					}
				}
			}

			$post_object = apply_filters('sf_post', $post_object);
			return $post_object;
		}
		return false;
	}
	function get_text_words($text, $count)
	{
		$tr = explode(' ', strip_tags(strip_shortcodes($text)));
		$s = "";
		for($i = 0; $i < $count && $i < sizeof($tr); $i++)
		{
			$s[] = $tr[$i];
		}
		return implode(' ', $s);
	}
	function enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'ajaxy-sf-search', AJAXY_SF_PLUGIN_URL."js/sf.js", array('jquery'), '1.0.1', true );
		wp_enqueue_script( 'ajaxy-sf-selective', AJAXY_SF_PLUGIN_URL."js/sf_selective.js", array('jquery'), '1.0.0', true );
		$this->enqueue_styles();
	}
	function enqueue_styles() {
		$themes = $this->get_installed_themes(AJAXY_THEMES_DIR, 'themes');
		$style = AJAXY_SF_PLUGIN_URL."themes/default/style.css";
		$theme = $this->get_style_setting('theme');
		if(isset($themes[$theme])){
			$style = $themes[$theme]['stylesheet_url'];
		}
		if($theme != 'blank') {
			if($this->get_style_setting('rtl_theme', 0) == 1) {
				wp_enqueue_style( 'ajaxy-sf-common', AJAXY_SF_PLUGIN_URL."themes/common-rtl.css" );
			}else{
				wp_enqueue_style( 'ajaxy-sf-common', AJAXY_SF_PLUGIN_URL."themes/common.css" );
			}
			wp_enqueue_style( 'ajaxy-sf-selective', AJAXY_SF_PLUGIN_URL."themes/selective.css" );
			wp_enqueue_style( 'ajaxy-sf-theme', $style );
		}
	}
	function head()
	{
		$css = $this->get_style_setting('css');
		?>
		<!-- AJAXY SEARCH V <?php echo AJAXY_SF_VERSION; ?>-->
		<?php if(trim($css) != ''): ?>
		<style type="text/css"><?php echo $css; ?></style>
		<?php
		endif;
		if(is_admin()) {?>
			<link rel="stylesheet" type="text/css" href="<?php echo AJAXY_SF_PLUGIN_URL; ?>/admin/css/styles.css" />
			<?php
		}
		$settings = array(
			'label' => $this->get_style_setting('search_label', 'Search'),
			'expand' => $this->get_style_setting('expand', false)
		);
		
		$live_search_settings = json_encode(
			array(
				'expand' => $settings['expand']
				,'searchUrl' => $this->get_style_setting('search_url',  home_url().'/?s=%s')
				,'text' => $settings['label']
				,'delay' => $this->get_style_setting('delay', 500)
				,'iwidth' => $this->get_style_setting('width', 180) 
				,'width' => $this->get_style_setting('results_width', 315) 
				,'ajaxUrl' => $this->get_ajax_url()
				,'rtl' => $this->get_style_setting('rtl_theme', 0)
			)
		);
		?>
		<script type="text/javascript">
			/* <![CDATA[ */
				var sf_position = '<?php echo $this->get_style_setting('results_position', 0); ?>';
				var sf_templates = <?php echo json_encode($this->get_templates('more', 'more')); ?>;
				var sf_input = '<?php echo (trim($this->get_style_setting('input_id', '.sf_input')) == "" ? '.sf_input' : $this->get_style_setting('input_id', '.sf_input')); ?>';
				jQuery(document).ready(function(){
					jQuery(sf_input).ajaxyLiveSearch(<?php echo $live_search_settings; ?>);
					jQuery(".sf_ajaxy-selective-input").keyup(function() {
						var width = jQuery(this).val().length * 8;
						if(width < 50) {
							width = 50;
						}
						jQuery(this).width(width);
					});
					jQuery(".sf_ajaxy-selective-search").click(function() {
						jQuery(this).find(".sf_ajaxy-selective-input").focus();
					});
					jQuery(".sf_ajaxy-selective-close").click(function() {
						jQuery(this).parent().remove();
					});
				});
			/* ]]> */
		</script>
		<?php
	}
	function get_ajax_url(){
		if(defined('ICL_LANGUAGE_CODE')){
			return admin_url('admin-ajax.php').'?lang='.ICL_LANGUAGE_CODE;
		}
		if(function_exists('qtrans_getLanguage')){

			return admin_url('admin-ajax.php').'?lang='.qtrans_getLanguage();
		}
		return admin_url('admin-ajax.php');
	}
	function footer(){
		//echo $script;
	}
	function get_shortcode()
	{
		if(isset($_POST['sf'])) {
			$postData = $_POST['sf']['style'];
			$m = array();
			$border = "";
			foreach($postData as $key => $value) {
				if(!empty($value)) {
					switch($key) {
						case "b_width":
							$border = $value."px ";
							break;
						case "b_type" :
							$border .= $value." ";
							break;
						case "b_color" :
							$border .= "#".$value." ";
							break;
						case "width" :
							$m[] = 'iwidth="'.$value.'"';
							break;
						case "results_width" :
							$m[] = 'width="'.$value.'"';
							break;
						case "post_types" :
							$m[] = 'post_types="'.implode(',', $value).'"';
							break;
						default:
							$m[] = $key.'="'.$value.'"';
							
					}
				}
			}
			if($border != ""){
				$m[] = 'border="'.trim($border).'"';
			}
			echo '[ajaxy-live-search '.implode(' ', $m).']';
		}
		//print_r($_POST);
		exit;
	}
	function get_search_results()
	{
		$results = array();
		$sf_value = apply_filters('sf_value', $_POST['sf_value']);
		if(!empty($sf_value))
		{
			//filter taxonomies if set
			$arg_taxonomies = isset($_POST['taxonomies']) && trim($_POST['taxonomies']) != "" ? explode(',', trim($_POST['taxonomies'])) : array();
			// override post_types from input
			$arg_post_types = isset($_POST['post_types']) && trim($_POST['post_types']) != "" ? explode(',', trim($_POST['post_types'])) : array();
			
			$search = $this->get_search_objects(false, false, $arg_post_types, $arg_taxonomies);
			$author_searched = false;
			$authors = array();
			foreach($search as $key => $object)
			{
				if($object['type'] == 'post_type') {
					$posts_result = $this->posts($sf_value, $object['name']);
					if(sizeof($posts_result) > 0) {
						$results[$object['name']][0]['all'] = $posts_result;
						$results[$object['name']][0]['template'] = $this->get_templates($object['name'], 'post_type');
						$results[$object['name']][0]['title'] = $object['label'];
						$results[$object['name']][0]['class_name'] = 'sf_item'.(in_array($object['name'], self::$woocommerce_post_types) ? ' woocommerce': '');
					}
				}
				elseif($object['type'] == 'taxonomy') {
					if($object['show_posts']) {
						$taxonomy_result = $this->category($sf_value, $object['name'], $object['show_posts']);
						if(sizeof($taxonomy_result) > 0) {
							$cnt = 0;
							foreach($taxonomy_result as $key => $val) {
								if(sizeof($val['posts']) > 0) {
									$results[$object['name']][$cnt]['all'] = $val['posts'];
									$results[$object['name']][$cnt]['template'] = $this->get_templates($object['name'], 'taxonomy');
									$results[$object['name']][$cnt]['title'] = $object['label'];
									$results[$object['name']][$cnt]['class_name'] = 'sf_category';
									$cnt ++;
								}
							}
						}
					}else{
						$taxonomy_result = $this->category($sf_value, $object['name']);
						if(sizeof($taxonomy_result) > 0) {
							$results[$object['name']][0]['all'] = $taxonomy_result;
							$results[$object['name']][0]['template'] = $this->get_templates($object['name'], 'taxonomy');
							$results[$object['name']][0]['title'] = $object['label'];
							$results[$object['name']][0]['class_name'] = 'sf_category';
						}
					}
				}elseif($object['type'] == 'role') {
					$users = array();
					if(!$author_searched) {
						$authors = $this->author($sf_value, $object['name']);
						$users = $this->filter_authors_by_role($authors, $object['name']);
						$author_searched = true;
					}else{
						$users = $this->filter_authors_by_role($authors, $object['name']);
					}
					if(sizeof($users) > 0) {
						$results[$object['name']][0]['all'] = $users;
						$results[$object['name']][0]['template'] = $this->get_templates($object['name'], 'author');
						$results[$object['name']][0]['title'] = $object['label'];
						$results[$object['name']][0]['class_name'] = 'sf_category';
					}
				}
			}
			$results = apply_filters('sf_results', $results);
			echo json_encode($results);
		}
		do_action( 'sf_value_results', $sf_value, $results);
		exit;
	}
	function get_installed_themes($themeDir, $themeFolder){
		$dirs = array();
		if ($handle = opendir($themeDir)) {
		  while (($file = readdir($handle)) !== false) {
			if('dir' == filetype($themeDir.$file) ){
				if(trim($file) != '.' && trim($file) != '..'){ 
					$dirs[] = $file;
				}
			}
		  }
		  closedir($handle);
		}
		$themes = array();
		if(sizeof($dirs) > 0){
			foreach($dirs as $dir){
				if(file_exists($themeDir.$dir.'/style.css')){
					$themes[$dir] = array(
								'title' => $dir,
								'stylesheet_dir' => $themeDir.$dir.'/style.css', 
								'stylesheet_url' => plugins_url( $themeFolder.'/'.$dir.'/style.css', __FILE__),
								'dir' => $themeDir.$dir,
								'url' => plugins_url( $themeFolder.'/'.$dir , __FILE__ )
								);
				}
			}
		}
		return $themes;
	}
	function admin_notice()
	{
		global $current_screen;
		if($current_screen->parent_base == 'ajaxy-page' && isset($_GET['ajaxy-tdismiss'])) {
			update_option('ajaxy-tdismiss', 2);
		}
		elseif(isset($_GET['ajaxy-tdismiss'])){
			update_option('ajaxy-tdismiss', 1);
		}
		$dismiss = (int)get_option('ajaxy-tdismiss');
		if(!class_exists ( 'AjaxyTracker' ) && (($dismiss != 1 && $dismiss != 2) || ($current_screen->parent_base == 'ajaxy-page' && $dismiss != 2))) {
			 echo '<div class="updated"><p><b>Ajaxy:</b> Track your live search and improve your website search with live search keyword tracker - <a href="'.get_admin_url().'plugin-install.php?tab=search&s=ajaxy+live+search+tracker&plugin-search-input=Search+Plugins">Download</a> | <a href="'.(strpos( $_SERVER['REQUEST_URI'], '?') !== false ? $_SERVER['REQUEST_URI'].'&ajaxy-tdismiss=1' : $_SERVER['REQUEST_URI'].'?ajaxy-tdismiss=1').'">No Thanks - Dismiss</a></p></div>';
		}
	}
	function form($settings)
	{
		$form = '
		<!-- Ajaxy Search Form v'.AJAXY_SF_VERSION.' -->
		<div id="'.$settings['id'].'" class="sf_container">
			<form role="search" method="get" class="searchform" action="' . home_url( '/' ) . '" >
				<div>
					<label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
					<div class="sf_search" style="border:'.$settings['border'].'">
						<span class="sf_block">
							<input style="width:'.($settings['width']).'px;" class="sf_input" autocomplete="off" type="text" value="' . (get_search_query() == '' ? $settings['label'] : get_search_query()). '" name="s"/>
							<button class="sf_button searchsubmit" type="submit"><span class="sf_hidden">'. esc_attr__(__('Search')) .'</span></button>
						</span>
					</div>
				</div>
			</form>
		</div>';
		if($settings['credits'] == 1) {
			$form = $form.'<a style="display:none" href="http://www.ajaxy.org">Powered by Ajaxy</a>';
		}
		return $form;
	}	
	function selective_input($settings)
	{
		$selective_input = '
		<!-- Ajaxy Selective Search Input v'.AJAXY_SF_VERSION.' -->
		<div id="'.$settings['id'].'" class="sf_ajaxy-selective-search" style="border:'.$settings['border'].';width:'.($settings['width']).'px;">
			<input class="sf_ajaxy-selective-input" type="text" placeholder="'.$settings['label'].'" value=""/>
		</div>';
		if(isset($settings['credits']) && $settings['credits'] == 1) {
			$selective_input = $selective_input.'<a style="display:none" href="http://www.ajaxy.org">Powered by Ajaxy</a>';
		}
		return $selective_input;
	}
	function form_shortcode($atts = array()) {
		$m = uniqid();
		$scat = (array)$this->get_setting('category');
		$settings = array(
			'id' => $m,
			'label' => $this->get_style_setting('search_label', 'Search'),
			'expand' => $this->get_style_setting('expand', 0),
			'width' => $this->get_style_setting('width', 180),
			'border' => $this->get_style_setting('border-width', '1') . "px " . $this->get_style_setting('border-type', 'solid') . " #" .$this->get_style_setting('border-color', 'dddddd'),
			'credits' => $this->get_style_setting('credits', 1 ),
			'show_category' => $scat['show'],
			'show_post_category' => $scat['ushow'],
			'post_types' => ''
		);
		if($settings['expand'] == 1){
			$settings['width'] = $settings['expand'];
		}
		$settings = shortcode_atts( $settings, $atts, 'ajaxy-live-search-layout' ) ;
		$form = $this->form($settings);
		
		
		$live_search_settings = array(
			'expand' => $settings['expand']
			,'searchUrl' => $this->get_style_setting('search_url',  home_url().'/?s=%s')
			,'text' => $settings['label']
			,'delay' => $this->get_style_setting('delay', 500)
			,'iwidth' => $this->get_style_setting('width', 180) 
			,'width' => $this->get_style_setting('results_width', 315) 
			,'ajaxUrl' => $this->get_ajax_url()
			,'ajaxData' => 'sf_custom_data_'.$m
			,'search' => false
			,'rtl' => $this->get_style_setting('rtl_theme', 0)
		);

		$live_search_settings = shortcode_atts( $live_search_settings, $atts, 'ajaxy-live-search' ) ;
		
		$script = '
		<script type="text/javascript">
			/* <![CDATA[ */
				function sf_custom_data_'.$m.'(data){ 
					data.show_category = "'.$settings['show_category'].'";
					data.show_post_category = "'.$settings['show_post_category'].'";
					data.post_types = "'.$settings['post_types'].'";
					return data;
				}
				jQuery(document).ready(function(){
					jQuery("#'.$m.' .sf_input").ajaxyLiveSearch('.json_encode($live_search_settings).');					
				});
			/* ]]> */
		</script>';
		return $form.$script;
	}	
	function selective_input_shortcode($atts = array()) {
		$m = uniqid();
		$scat = (array)$this->get_setting('category');
		$settings = array(
			'id' => $m,
			'label' => $this->get_style_setting('search_label', 'Search'),
			'expand' => $this->get_style_setting('expand', 0),
			'width' => $this->get_style_setting('width', 180),
			'border' => $this->get_style_setting('border-width', '1') . "px " . $this->get_style_setting('border-type', 'solid') . " #" .$this->get_style_setting('border-color', 'dddddd'),
			'credits' => $this->get_style_setting('credits', 0 ),
			'show_category' => $scat['show'],
			'show_post_category' => $scat['ushow'],
			'post_types' => '',
			'name' => 'selective_search',
			'name-type' => 'array',
			'value' => '{post_type}'
		);
		if($settings['expand'] == 1){
			$settings['width'] = $settings['expand'];
		}

		$settings = shortcode_atts( $settings, $atts, 'ajaxy-selective-search-layout' ) ;

		$selective_input = $this->selective_input((array)$settings);

		$live_search_settings = array(
			'expand' => $settings['expand']
			,'searchUrl' => $this->get_style_setting('search_url',  home_url().'/?s=%s')
			,'text' => $settings['label']
			,'delay' => $this->get_style_setting('delay', 500)
			,'iwidth' => $this->get_style_setting('width', 180) 
			,'width' => $this->get_style_setting('results_width', 315) 
			,'ajaxUrl' => $this->get_ajax_url()
			,'ajaxData' => 'sf_custom_data_'.$m
			,'callback' => 'sf_load_data_'.$m
			,'search' => ''
			,'rtl' => $this->get_style_setting('rtl_theme', 0)
		);

		$live_search_settings = shortcode_atts( $live_search_settings, $atts, 'ajaxy-live-search' ) ;
		
		$script = '
		<script type="text/javascript">
			/* <![CDATA[ */
				function sf_custom_data_'.$m.'(data){ 
					data.show_category = "'.$settings['show_category'].'";
					data.show_post_category = "'.$settings['show_post_category'].'";
					data.post_types = "'.$settings['post_types'].'";
					return data;
				}function sf_load_data_'.$m.'(object, item){ 
					var data = jQuery("body").data("sf_results");
					var rType = jQuery(item).attr("result-type");
					var name_type = "'.$settings['name-type'].'";
					var name_value = "'.$settings['value'].'";
					if(rType == "object") {
						var iType = jQuery(item).attr("index-type");
						var iArray = jQuery(item).attr("index-array");
						var index = jQuery(item).attr("index");
						var itemObject = data[iType][iArray];
						for (var key in itemObject.all[index]) {	
							var reg = new RegExp("{"+key+"}","gi");
							name_value = name_value.replace(reg, itemObject.all[index][key]);
						}
						sf_addItem(jQuery(object.element).parent(), itemObject.all[index].post_title, "'.$settings['name'].($settings['name-type'] == 'array' ? '[]':'').'", name_type, name_value); 
					}else if(rType == "array") {
						var index = jQuery(item).attr("index");
						var itemObject = data[index];
						sf_addItem(jQuery(object.element).parent(), itemObject, "'.$settings['name'].($settings['name-type'] == 'array' ? '[]':'').'", name_type, itemObject); 
					}
					
				}
				jQuery(document).ready(function(){
					jQuery("#'.$m.' .sf_ajaxy-selective-input").ajaxyLiveSearch('.json_encode($live_search_settings).');					
				});
			/* ]]> */
		</script>';
		return $selective_input.$script;
	}
}
add_filter('sf_category_query', 'sf_category_query', 4, 10);
function sf_category_query($query, $search, $excludes, $limit){
	global $wpdb;
	$wpml_lang_code = (defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE: false);
	if(	$wpml_lang_code ) {
		if(sizeof($excludes) > 0){
			$excludes = " AND $wpdb->terms.term_id NOT IN (".implode(",", $excludes).")";
		}
		else{
			$excludes = "";
		}
		$query = "select * from (select distinct($wpdb->terms.name), $wpdb->terms.term_id,  $wpdb->term_taxonomy.taxonomy,  $wpdb->term_taxonomy.term_taxonomy_id from $wpdb->terms, $wpdb->term_taxonomy where name like '%%%s%%' and $wpdb->term_taxonomy.taxonomy<>'link_category' and $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id $excludes limit 0, ".$limit.") as c, ".$wpdb->prefix."icl_translations as i where c.term_taxonomy_id = i.element_id and i.language_code = %s and SUBSTR(i.element_type, 1, 4)='tax_' group by c.term_id";
		$query = $wpdb->prepare($query,  $search, $wpml_lang_code);
		return $query;
	}
	return $query;
}
add_filter('sf_posts_query', 'sf_posts_query', 5, 10);
function sf_posts_query($query, $search, $post_type, $excludes, $search_content, $order_results, $limit){
	global $wpdb;
	$wpml_lang_code = (defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE: false);
	if(	$wpml_lang_code ) {
		if(sizeof($excludes) > 0){
			$excludes = " AND $wpdb->posts.ID NOT IN (".implode(",", $excludes).")";
		}
		else{
			$excludes = "";
		}
		//$order_results = (!empty($order_results) ? " order by ".$order_results : "");
		$query = $wpdb->prepare("select * from (select $wpdb->posts.ID from $wpdb->posts where (post_title like '%%%s%%' ".($search_content == true ? "or post_content like '%%%s%%')":")")." and post_status='publish' and post_type='".$post_type."' $excludes $order_results limit 0,".$limit.") as p, ".$wpdb->prefix."icl_translations as i where p.ID = i.element_id and i.language_code = %s group by p.ID",  ($search_content == true ? array($search, $search, $wpml_lang_code): array($search, $wpml_lang_code)));
		return $query;
	}
	return $query;
}
function ajaxy_search_form($settings = array())
{
	global $AjaxyLiveSearch;
	echo $AjaxyLiveSearch->form_shortcode($settings);
}
global $AjaxyLiveSearch;
$AjaxyLiveSearch = new AjaxyLiveSearch();


?>