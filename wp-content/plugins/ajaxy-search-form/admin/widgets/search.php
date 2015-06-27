<?php

class AJAXY_SF_WIDGET extends WP_Widget 
{
	function AJAXY_SF_WIDGET() {
		parent::WP_Widget( false, $name = 'Ajaxy live search' );	
	}
	function widget( $args, $instance ) 
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		echo $before_title.$title.$after_title;
		$this->searchform($instance);
		echo $after_widget;
	}
	function form($instance)
	{
		$text_before = isset($instance['text_before']) ? $instance['text_before'] : '' ;
		$text_after = isset($instance['text_after']) ? $instance['text_after'] : '' ;
		$label = isset($instance['label']) ? $instance['label'] : '' ;
		$expand = isset($instance['expand']) ? $instance['expand'] : '' ;
		$width = isset($instance['width']) ? $instance['width'] : '' ;
		$delay = isset($instance['delay']) ? $instance['delay'] : '' ;
		$border = isset($instance['border']) ? $instance['border'] : '' ;
		$credits = isset($instance['credits']) ? $instance['credits'] : '' ;
		$show_category = isset($instance['show_category']) ? $instance['show_category'] : '' ;
		$show_post_category = isset($instance['show_post_category']) ? $instance['show_post_category'] : '' ;
		$post_types = isset($instance['post_types']) ? $instance['post_types'] : '' ;
		
		?>
		<p><label for="<?php echo $this->get_field_id( 'text_before' ); ?>"><?php _e( 'Text before:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'text_before' ); ?>" name="<?php echo $this->get_field_name( 'text_before' ); ?>" type="text" value="<?php echo $text_before; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'text_after' ); ?>"><?php _e( 'Text after:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'text_after' ); ?>" name="<?php echo $this->get_field_name( 'text_after' ); ?>" type="text" value="<?php echo $text_after; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'label' ); ?>"><?php _e( 'Search label:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" type="text" value="<?php echo $label; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'expand' ); ?>"><?php _e( 'Expand width:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'expand' ); ?>" name="<?php echo $this->get_field_name( 'expand' ); ?>" type="text" value="<?php echo $expand; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'delay' ); ?>"><?php _e( 'Delay:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'delay' ); ?>" name="<?php echo $this->get_field_name( 'delay' ); ?>" type="text" value="<?php echo $delay; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'border' ); ?>"><?php _e( 'Border:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'border' ); ?>" name="<?php echo $this->get_field_name( 'border' ); ?>" type="text" value="<?php echo $border; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'credits' ); ?>"><input class="widefat" id="<?php echo $this->get_field_id( 'credits' ); ?>" name="<?php echo $this->get_field_name( 'credits' ); ?>" type="checkbox" <?php echo $credits == 1 ? 'checked="checked"' : ''; ?> value="1" /><?php _e( 'Credits:' ); ?></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_category' ); ?>"><input class="widefat" id="<?php echo $this->get_field_id( 'show_category' ); ?>" name="<?php echo $this->get_field_name( 'show_category' ); ?>" type="checkbox" <?php echo $show_category == 1 ? 'checked="checked"' : ''; ?> value="1" /><?php _e( 'Show Category Results:' ); ?></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_post_category' ); ?>"><input class="widefat" id="<?php echo $this->get_field_id( 'show_post_category' ); ?>" name="<?php echo $this->get_field_name( 'show_post_category' ); ?>" type="checkbox" <?php echo $show_post_category == 1 ? 'checked="checked"' : ''; ?> value="1" /><?php _e( 'Show Post Category Results:' ); ?></label></p>
		<p><label for="<?php echo $this->get_field_id( 'post_types' ); ?>"><?php _e( 'Post types (if set only these post types will be search):' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'post_types' ); ?>" name="<?php echo $this->get_field_name( 'post_types' ); ?>" type="text" value="<?php echo $post_types; ?>" /></label></p>
		
	<?php
	}
	function searchform($instance)
	{
		echo $instance['text_before'] ;
		$label = $instance['label'] ;
		$setings = array();
		if(trim($instance['label']) != "") {
			$setings['label'] = $instance['label'];
		}if(trim($instance['expand']) != "") {
			$setings['expand'] = $instance['expand'];
		}if(trim($instance['width']) != "") {
			$setings['width'] = $instance['width'];
		}if(trim($instance['delay']) != "") {
			$setings['delay'] = $instance['delay'];
		}if(trim($instance['border']) != "") {
			$setings['border'] = $instance['border'];
		}if(isset($instance['credits'])) {
			$setings['credits'] = (int)$instance['credits'];
		}if(isset($instance['show_category'])) {
			$setings['show_category'] = (int)$instance['show_category'];
		}if(isset($instance['show_post_category'])) {
			$setings['show_post_category'] = (int)$instance['show_post_category'];
		}if(trim($instance['post_types']) != "") {
			$setings['post_types'] = $instance['post_types'];
		}
		ajaxy_search_form($setings);
		echo $instance['text_after'] ;
	}
	function update( $new_instance, $instance )
	{	
		$old_instance = $instance;
		$old_instance['text_before'] = $new_instance['text_before'] ;
		$old_instance['text_after'] = $new_instance['text_after'] ;
		
		$old_instance['label'] = $new_instance['label'];
		$old_instance['expand'] = $new_instance['expand'];
		$old_instance['width'] = $new_instance['width'];
		$old_instance['delay'] = $new_instance['delay'];
		$old_instance['border'] = $new_instance['border'];
		$old_instance['credits'] = (int)$new_instance['credits'];
		$old_instance['show_category'] = (int)$new_instance['show_category'];
		$old_instance['show_post_category'] = (int)$new_instance['show_post_category'];
		$old_instance['post_types'] = $new_instance['post_types'];

        return $old_instance;
	}
}

?>