<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WooDojo_Tab_Grouping_Table extends WP_List_Table {
	public $per_page = 5;

	/**
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct () {
		global $status, $page;

		$args = array(
	            'singular'  => __( 'tab-group', 'woodojo' ),     //singular name of the listed records
	            'plural'    => __( 'tab-groups', 'woodojo' ),   //plural name of the listed records
	            'ajax'      => false        //does this table support ajax?

	    );

		$this->data = array();

	    parent::__construct( $args );
	} // End __construct()

	/**
	 * Text to display if no items are present.
	 * @since  1.0.0
	 * @return  void
	 */
	public function no_items () {
	    _e( 'No tab groups found.', 'woodojo' );
	} // End no_items(0)

	/**
	 * The content of each column.
	 * @param  array $item         The current item in the list.
	 * @param  string $column_name The key of the current column.
	 * @since  1.0.0
	 * @return string              Output for the current column.
	 */
	public function column_default ( $item, $column_name ) {
	    switch( $column_name ) { 
	        case 'title':
	        case 'tabs':
	        case 'slug':
	            return $item[$column_name];
	        break;
	    }
	} // End column_default()

	/**
	 * Retrieve an array of sortable columns.
	 * @since  1.0.0
	 * @return array
	 */
	public function get_sortable_columns () {
	  return array();
	} // End get_sortable_columns()

	/**
	 * Retrieve an array of columns for the list table.
	 * @since  1.0.0
	 * @return array Key => Value pairs.
	 */
	public function get_columns () {
        $columns = array(
            'title' => __( 'Title', 'woodojo' ), 
            'slug' => __( 'Slug', 'woodojo' ),  
            'tabs' => __( 'Tabs', 'woodojo' )
        );
         return $columns;
    } // End get_columns()

    /**
     * Content for the "title" column.
     * @param  array  $item The current item.
     * @since  1.0.0
     * @return string       The content of this column.
     */
	public function column_title ( $item ) {
		$edit_nonce = wp_create_nonce( 'woodojo-edit-screen' );

		$actions = array(
            'edit'      => sprintf('<a href="?page=%s&screen=%s&slug=%s&_wpnonce=' . $edit_nonce . '">' . __( 'Edit', 'woodojo' ) . '</a>',$_REQUEST['page'],'edit',$item['slug']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&slug=%s&_wpnonce=' . wp_create_nonce( 'woodojo-tab-grouping-delete-tab-grouping' ) . '">' . __( 'Delete', 'woodojo' ) . '</a>',$_REQUEST['page'], 'delete-tab-grouping', $item['slug']),
        );

  		return sprintf('%1$s %2$s', sprintf('<strong><a href="?page=%s&screen=%s&slug=%s&_wpnonce=' . $edit_nonce . '" class="row-title">%4$s</a></strong>',$_REQUEST['page'],'edit',$item['slug'], $item['title']), $this->row_actions( $actions ) );
	} // End column_title()

	/**
     * Content for the "tabs" column.
     * @param  array  $item The current item.
     * @since  1.0.0
     * @return string       The content of this column.
     */
	public function column_tabs ( $item ) {
		return join( ', ', (array)$item['tabs'] );
	} // End column_tabs()

	/**
	 * Retrieve an array of possible bulk actions.
	 * @since  1.0.0
	 * @return array
	 */
	public function get_bulk_actions () {
	  $actions = array();
	  return $actions;
	} // End get_bulk_actions()

	/**
	 * Prepare an array of items to be listed.
	 * @since  1.0.0
	 * @return array Prepared items.
	 */
	public function prepare_items () {
	  $columns  = $this->get_columns();
	  $hidden   = array();
	  $sortable = $this->get_sortable_columns();
	  $this->_column_headers = array( $columns, $hidden, $sortable );

	  $total_items = count( $this->data );

	  // only ncessary because we have sample data
	  $this->found_data = $this->data;

	  $this->set_pagination_args( array(
	    'total_items' => $total_items,                  //WE have to calculate the total number of items
	    'per_page'    => $total_items                   //WE have to determine how many items to show on a page
	  ) );
	  $this->items = $this->found_data;
	} // End prepare_items()
} // End Class
?>