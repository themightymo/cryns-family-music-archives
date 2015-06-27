<?php
/*
Plugin Name: Recent Posts for Custom Post Type
Description: Creates "Recent Posts" widget for Custom Post Types
Version: 1.0
Author: Jimmy Ngu
Author URI: http://jimmyngu.com/

Ported from default WP_widget_recent_posts_custom widget

*/

if( !class_exists("Widget_Recent_Custom_Posts")){
class Widget_Recent_Custom_Posts extends WP_Widget {

	function Widget_Recent_Custom_Posts() {
		
		$widget_ops = array('classname' => 'widget_recent_entries_custom', 'description' => __( "The most recent posts for a selected custom post type on your site") );
		parent::WP_Widget('recent-custom-posts', __('Recent Custom Posts'), $widget_ops);
		$this->alt_option_name = 'widget_recent_entries_custom';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_posts_custom', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title'], $instance, $this->id_base);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;
		
		if( !$post_type = $instance['post_type'] )
			$post_type = 'post';
		
		$r = new WP_Query(array('post_type' => $post_type, 'showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'caller_get_posts' => 1));
		
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_posts_custom', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['post_type'] = $new_instance['post_type'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries_custom']) )
			delete_option('widget_recent_entries_custom');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_posts_custom', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
			$number = 5;
			
		$posttypes = get_post_types(null, 'objects');
		$posttypes_opt = array();
		
		foreach( $posttypes as $id => $obj ) {
			if(!$obj->_builtin)
				$posttypes_opt[$id] = $obj->labels->name;
		}
		
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		
        <p><label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:'); ?></label>
		<select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
       		<?php foreach($posttypes_opt as $id => $post_type){ ?>
            		<option value="<?php echo $id?>" <?php echo selected($id, $instance['post_type'])?>><?php echo $post_type?></option>
			<?php } ?>
        </select></p>

<?php
	}
}

add_action('widgets_init', create_function("","register_widget('Widget_Recent_Custom_Posts');"));

}