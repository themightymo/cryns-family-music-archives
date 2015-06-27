<?php
/**
 * List Table class.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 * @access private
 */
 
class WP_SF_List_Table extends WP_List_Table {

	var $callback_args;
	var $objects = array();
	var $public = true;
	var $setting_prefix = '';
	
	function WP_SF_List_Table($objects, $public = true, $setting_prefix = '') {
		parent::__construct( array(
			'plural' => 'Settings',
			'singular' => 'Setting',
		) );
		$this->objects = $objects;
		$this->public = $public;
		$this->setting_prefix = $setting_prefix;
	}
	var $row_class = '';
	function ajax_user_can() {
		return true;
	}

	function prepare_items() {
		$search = !empty( $_REQUEST['s'] ) ? trim( stripslashes( $_REQUEST['s'] ) ) : '';

		$args = array(
			'search' => $search,
			'page' => $this->get_pagenum(),
			'number' => 10,
		);

		if ( !empty( $_REQUEST['orderby'] ) )
			$args['orderby'] = trim( stripslashes( $_REQUEST['orderby'] ) );

		if ( !empty( $_REQUEST['order'] ) )
			$args['order'] = trim( stripslashes( $_REQUEST['order'] ) );

		$this->callback_args = $args;

		$this->set_pagination_args( array(
			'total_items' => sizeof($this->objects),
			'per_page' => 10,
		) );
	}

	function has_items() {
		// todo: populate $this->items in prepare_items()
		return true;
	}

	function get_bulk_actions() {
		$actions = array();
		$actions['hide'] = __( 'Hide from results' );
		$actions['show'] = __( 'Show in results' );

		return $actions;
	}

	function current_action() {
		if ( isset( $_REQUEST['action'] ) && ( 'hide' == $_REQUEST['action'] || 'hide' == $_REQUEST['action2'] ) )
			return 'bulk-hide';

		return parent::current_action();
	}

	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'title'        => __( 'Title' ),
			'type'    => __( 'Type' ),
			'search_setting'=> __( 'Search setting' ),
			'show_on_search' => __( 'Search' ),
			'limit_results' => __( 'Limit' ),	
			'order'        	=> __( 'Order' )
		);

		return $columns;
	}
	function get_column_info() {
		if ( isset( $this->_column_headers ) )
			return $this->_column_headers;

		$screen = get_current_screen();

		$columns = $this->get_columns();
		$hidden = array();

		$this->_column_headers = array( $columns, $hidden, $this->get_sortable_columns() );

		return $this->_column_headers;
	}

	function get_sortable_columns() {
		return array(
		);
	}

	function display_rows_or_placeholder() {
		$args = wp_parse_args( $this->callback_args, array(
			'page' => 1,
			'number' => 20,
			'search' => '',
			'hide_empty' => 0
		) );

		extract( $args, EXTR_SKIP );

		$args['offset'] = $offset = ( $page - 1 ) * $number;

		// convert it to table rows
		$out = '';
		$count = 0;
		$orderby = 'order';
		$_REQUEST['order'] = (isset($_REQUEST['order']) ? $_REQUEST['order'] :'');
		$_REQUEST['orderby'] = (isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] :'');
		$order = (in_array($_REQUEST['order'], array('asc','desc')) ? $_REQUEST['order']: 'asc');
		switch($_REQUEST['orderby']){
			case 'n':
				$orderby = 'name';
				break;
			case 'o':
				$orderby = 'order';
				break;
			case 't':
				$orderby = 'type';
				break;
			default:
				$orderby = 'order';
				break;
		}

		if(!empty($this->objects)){
			foreach($this->objects as $object){
				$this->single_row($object);
			}
		}
		if ( empty( $this->objects ) ) {
			list( $columns, $hidden ) = $this->get_column_info();
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		} else {
			echo $out;
		}
	}

	function single_row( $field, $level = 0 ) {
		global $AjaxyLiveSearch;
		$setting = (array)$AjaxyLiveSearch->get_setting($this->setting_prefix.$field['name'], $this->public);
		$field['settings'] = $setting;
		$add_class = ($setting['show'] == 1 ? 'row-yes':'row-no');
		$this->row_class = ( $this->row_class == '' ? 'alternate' : '' );
		
		echo '<tr id="type-' . $field['name'] . '" class="' . $this->row_class." ". $add_class . '">';
		echo $this->single_row_columns( $field );
		echo '</tr>';
	}

	function column_cb( $field ) {
		return '<input type="checkbox" name="template_id[]" value="'.$field['name'].'" />';
	}
	function column_show_on_search( $field ) {
		$setting = $field['settings'];
		return '<span>'.($setting['show'] == 1 ? 'Yes':'No').'</span>';
	}
	function column_search_setting( $field ) {
		$setting = $field['settings'];
		return ($setting['search_content'] == 0 ? 'Only title':'both title and content');
	}
	function column_limit_results( $field ) {
		$setting = $field['settings'];
		return $setting['limit'];
	}
	function column_order( $field ) {
		$setting = $field['settings'];
		return $setting['order'];
	}
	function column_type( $field ) {
		if($field['type'] == 'taxonomy') {
			$link = admin_url()."edit-tags.php?taxonomy=".$field['name'];
		}elseif($field['type'] == 'post_type') {
			$link = admin_url()."edit.php?post_type=".$field['name'];
		}else{
			$link = 'javascript:;';
		}
		
		return '<a href="'.$link.'">'.$field['name'].'</a> ('.$field['type'].')';
	}
	function column_title( $field ) {
		global $AjaxyLiveSearch;
		
		$name =  $field['label'];
		
		$edit_link = menu_page_url('ajaxy_sf_admin', false).'&type='.$field['type'].'&name='.$field['name'].'&edit=1';
		

		$out = '<strong><a class="row-title" href="' . $edit_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $name ) ) . '">' . $name . '</a></strong><br />';

		$actions = array();

		$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit template & Settings' ) . '</a>';
		
		$setting = (array)$AjaxyLiveSearch->get_setting($this->setting_prefix.$field['name'], $this->public);
		if ($setting['show'] == 1 ):
			$actions['hide'] = "<a class='hide-field' href='" . wp_nonce_url( menu_page_url('ajaxy_sf_admin', false).'&amp;name='.$field['name'].'&amp;type='.$field['type'].'&amp;show=0&amp;tab='.$_GET['tab'], 'hide-post_type_' .$field['name'] ) . "'>" . __( 'Hide from results' ) . "</a>";
		else:
			$actions['show'] = "<a class='show-field' href='" . wp_nonce_url( menu_page_url('ajaxy_sf_admin', false).'&amp;name='.$field['name'].'&amp;type='.$field['type'].'&amp;show=1&amp;tab='.$_GET['tab'], 'show-post_type_' .$field['name'] ) . "'>" . __( 'show in results' ) . "</a>";
		endif;
		$out .= $this->row_actions( $actions );
		$out .= '<div class="hidden" id="inline_' . $field['name'] . '">';
		$out .= '<div class="name">' . $field['label']. '</div>';

		return $out;
	}
	function column_default( $field, $column_name ) {
		return $field[$column_name];
	}

	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 3.1.0
	 */
	function inline_edit() {
	}
}

?>
