<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WooDojo_Tab_Grouping {
	private $assets_url;
	private $screens_path;
	private $classes_path;

	private $token;
	private $name;
	private $menu_label;
	private $page_slug;

	/**
	 * Constructor.
	 * @param string $file The main file for this module.
	 * @since  1.0.0
	 */
	public function __construct ( $file ) {
		global $woodojo;
		$this->assets_url = trailingslashit( trailingslashit( plugins_url( '', $file ) ) . 'assets' );
		$this->screens_path = trailingslashit( trailingslashit( dirname( $file ) ) . 'screens' );
		$this->classes_path = trailingslashit( dirname( __FILE__ ) );

		add_action( 'admin_print_styles', array( &$woodojo->admin, 'admin_styles' ) );

		$this->token = 'woodojo';
		$this->id = $this->token;
		$this->name = __( 'Tab Grouping', 'woodojo' );
		$this->menu_label = __( 'Tab Grouping', 'woodojo' );
		$this->page_slug = 'tab-grouping';

		add_action( 'admin_menu', array( &$this, 'register_settings_screen' ) );

		add_action( 'admin_init', array( &$this, 'form_actions' ) );

		$this->capability = 'manage_options';
 		if ( is_admin() ) {
	 		add_filter( 'widget_update_callback', array( &$this, 'save_widget_form' ), 10, 2 );
	 		add_action( 'in_widget_form', array( &$this, 'widget_form_html' ), 10, 3 );
 		} else {
 			add_filter( 'widget_display_callback', array( &$this, 'filter_by_tab_group' ), 10, 3 );
 			add_filter( 'woodojo_tabs_headings', array( &$this, 'apply_tabs_filter' ) );
 		}
	} // End __construct()

	/**
	 * Register the admin screen.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings_screen () {
		$hook = add_submenu_page( 'woodojo', $this->name, $this->menu_label, 'manage_options', $this->page_slug, array( &$this, 'settings_screen' ) );

		add_action( 'admin_print_scripts-' . $hook, array( &$this, 'enqueue_scripts' ) );
		add_action( 'admin_print_styles-' . $hook, array( &$this, 'enqueue_styles' ) );
	} // End register_settings_screen()

	/**
	 * Render the admin screen.
	 * @access public
	 * @return void
	 */
	public function settings_screen () { ?>
	<div id="woodojo" class="wrap woodojo-tab-grouping">
	<?php screen_icon( 'woodojo' ); ?>
	<h2><?php echo $this->menu_label; ?></h2>
	<?php $this->display_messages(); ?>
	<?php
		// Require the WooDojo - Tabs widget.
		if ( ! class_exists( 'WooDojo_Widget_Tabs' ) ) { echo '<div class="error fade"><p>' . __( 'This feature requires the WooDojo "Tabs" widget to be active.', 'woodojo' ) . '</p></div>'; return; }

		$tabs = $this->get_tabs();

		$screen = 'list';
		if ( isset( $_GET['screen'] ) && ( 'edit' == $_GET['screen']  ) && isset( $_GET['slug'] ) && ( $this->grouping_exists( esc_attr( $_GET['slug'] ) ) ) ) {
			$screen = 'edit';
		}

		require_once( $this->screens_path . $screen . '-screen.php' );
	?>
	</div><!--/#woodojo-->
<?php
	} // End settings_screen()

	/**
	 * Enqueue scripts for the admin.
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->token . '-sortable', $this->assets_url . 'js/functions.js', array( 'jquery', 'jquery-ui-sortable' ), '1.0.0', true );
		wp_enqueue_script( $this->token . '-sortable' );
	} // End enqueue_scripts()

	/**
	 * Enqueue styles for the admin.
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->token . '-sortable', $this->assets_url . 'css/style.css' );
		wp_enqueue_style( $this->token . '-sortable' );
	} // End enqueue_styles()

	/**
	 * Form actions to handle the various CRUD tasks.
	 * @since  1.0.0
	 * @return void
	 */
	public function form_actions () {
		if ( ! isset( $_REQUEST['action'] ) || ! in_array( $_REQUEST['action'], array( 'add-tab-grouping', 'edit-tab-grouping', 'delete-tab-grouping' ) ) ) { return; }

		switch ( esc_attr( $_REQUEST['action'] ) ) {
			// Add.
			case 'add-tab-grouping':

			check_admin_referer( $this->token . '-add-grouping', $this->token . '-add-grouping' );

			if ( ! current_user_can( 'manage_options' ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			// Get the existing storage.
			$tabs = $this->get_tab_groups();

			// The redirect location.
			$location = admin_url( 'admin.php' );
			$location = add_query_arg( 'page', urlencode( $_REQUEST['page'] ), $location );

			$title = esc_html( $_REQUEST['grouping-name'] );
			$slug = sanitize_title_with_dashes( $_REQUEST['slug'], '', 'save' );
			if ( $slug == '' ) $slug = sanitize_title_with_dashes( $_REQUEST['grouping-name'], '', 'save' );

			// Store only the tabs we want to store.
			$selected_tabs = $this->sanitize_tabs( $_REQUEST['tabs'], $_REQUEST['tab-order'], array_keys( $this->get_tabs() ) );

			// Check if a grouping exists.
			if ( $this->grouping_exists( $slug ) ) {
				$location = add_query_arg( 'message', 7, $location ); // We already have a group with that slug.
			} else {
				$inserted = $this->insert_grouping( array( 'title' => $title, 'slug' => $slug, 'tabs' => $selected_tabs ) );
				if ( 0 == $inserted ) {
					$location = add_query_arg( 'message', 2, $location );
				} else {
					$location = add_query_arg( 'message', 1, $location );
				}
			}

			wp_redirect( $location );
			exit;
			break;

			// Edit.
			case 'edit-tab-grouping':

			check_admin_referer( $this->token . '-edit-tab-grouping', $this->token . '-edit-tab-grouping' );

			if ( ! current_user_can( 'manage_options' ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			// Get the existing storage.
			$tabs = $this->get_tab_groups();

			// The redirect location.
			$location = admin_url( 'admin.php' );
			$location = add_query_arg( 'page', urlencode( $_REQUEST['page'] ), $location );

			$title = esc_html( $_REQUEST['grouping-name'] );
			$slug = sanitize_title_with_dashes( $_REQUEST['slug'], '', 'save' );
			$old_slug = sanitize_title_with_dashes( $_REQUEST['old-slug'], '', 'save' );
			if ( $slug == '' ) $slug = sanitize_title_with_dashes( $_REQUEST['grouping-name'], '', 'save' );

			// Store only the tabs we want to store.
			$selected_tabs = $this->sanitize_tabs( $_REQUEST['tabs'], $_REQUEST['tab-order'], array_keys( $this->get_tabs() ) );

			// Check if a grouping exists.
			if ( ( strtolower( $slug ) != strtolower( $old_slug ) ) && $this->grouping_exists( $slug ) ) {
				$location = add_query_arg( '_wpnonce', wp_create_nonce( 'woodojo-tab-grouping-edit-screen' ), $location );
				$location = add_query_arg( 'slug', urlencode( $old_slug ), $location );
				$location = add_query_arg( 'screen', $this->token . '-edit-screen', $location );
				$location = add_query_arg( 'message', 7, $location ); // We already have a group with that slug.
			} else {
				$updated = $this->update_grouping( array( 'title' => $title, 'slug' => $slug, 'tabs' => $selected_tabs, 'old-slug' => $old_slug ) );
				if ( 0 == $updated ) {
					$location = add_query_arg( 'message', 4, $location ); // Update Error.
				} else {
					$location = add_query_arg( 'message', 3, $location ); // Update Success.
				}
			}

			wp_redirect( $location );
			exit;

			break;

			// Delete.
			case 'delete-tab-grouping':

			check_admin_referer( $this->token . '-tab-grouping-delete-tab-grouping', '_wpnonce' );

			if ( ! current_user_can( 'manage_options' ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			// Get the existing storage.
			$tabs = $this->get_tab_groups();

			// The redirect location.
			$location = admin_url( 'admin.php' );
			$location = add_query_arg( 'page', urlencode( $_GET['page'] ), $location );

			$slug = sanitize_title_with_dashes( $_GET['slug'], '', 'save' );

			$deleted = $this->delete_grouping( $slug );
			if ( 0 == $deleted ) {
				$location = add_query_arg( 'message', 6, $location );
			} else {
				$location = add_query_arg( 'message', 5, $location );
			}

			wp_redirect( $location );
			exit;
			break;
		}
	} // End form_actions()

	/**
	 * Display success/error messages, if available.
	 * @since  1.0.0
	 * @return void
	 */
	private function display_messages () {
		$message = $this->get_message( intval( $_GET['message'] ) );
		if ( is_array( $message ) && isset( $message['type'] ) && in_array( $message['type'], array( 'updated', 'error' ) ) && isset( $message['text'] ) ) {
			echo '<div class="' . esc_attr( $message['type'] ) . ' fade"><p>' . esc_attr( $message['text'] ) . '</p></div>' . "\n";
		}
	} // End display_messages()

	/**
	 * Return an array of message data according to a specified number.
	 * @since  1.0.0
	 * @param  int $int 		The number for the message to display.
	 * @return array/false      An array of correct, or false if not.
	 */
	private function get_message ( $int ) {
		$response = false;

		switch ( intval( $int ) ) {
			case 1:
				$response = array( 'type' => 'updated', 'text' => __( 'Grouping added successfully.', 'woodojo' ) );
			break;

			case 2:
				$response = array( 'type' => 'error', 'text' => __( 'There was an error while adding the grouping. Please try again.', 'woodojo' ) );
			break;

			case 3:
				$response = array( 'type' => 'updated', 'text' => __( 'Grouping updated successfully.', 'woodojo' ) );
			break;

			case 4:
				$response = array( 'type' => 'error', 'text' => __( 'There was an error while updating the grouping. Please try again.', 'woodojo' ) );
			break;

			case 5:
				$response = array( 'type' => 'updated', 'text' => __( 'Grouping deleted successfully.', 'woodojo' ) );
			break;

			case 6:
				$response = array( 'type' => 'error', 'text' => __( 'There was an error while deleting the grouping. Please try again.', 'woodojo' ) );
			break;

			case 7:
				$response = array( 'type' => 'error', 'text' => __( 'A grouping with this slug already exists.', 'woodojo' ) );
			break;

			default:
			break;
		}

		return $response;
	} // End get_message()

	/**
	 * Get the existing storage.
	 * @since  1.0.0
	 * @return array Array of existing storage.
	 */
	public function get_tab_groups () {
		return get_option( $this->token . '-tab-groups', array() );
	} // End get_tab_groups()

	/**
	 * Get tabs to be worked with.
	 * @since  1.0.0
	 * @return array Array of tabs.
	 */
	public function get_tabs () {
		// Setup tab pieces to be loaded in below.
		$tabs = array(
						'latest' => __( 'Latest', 'woodojo' ),
						'popular' => __( 'Popular', 'woodojo' ),
						'comments' => __( 'Comments', 'woodojo' ),
						'tags' => __( 'Tags', 'woodojo' )
					);

		// Allow child themes/plugins to filter here.
		$tabs = apply_filters( 'woodojo_tabs_headings', $tabs );

		return $tabs;
	} // End get_tabs()

	/**
	 * Prepare tabs for entry into the database.
	 * @since  1.0.0
	 * @param  array $posted     Selected tabs.
	 * @param  string $order     String to reference the tab order.
	 * @param  array $whitelist  Whitelist array of acceptable tabs.
	 * @return array             Prepared tabs array.
	 */
	public function sanitize_tabs ( $posted, $order, $whitelist ) {
		$tabs = array();

		if( ! is_array( $posted )) return $tabs;

		foreach ( $posted as $k => $v ) {
			if ( ! in_array( $v, $whitelist ) ) { unset( $posted[$k] ); }
		}

		if ( count( $posted ) > 0 ) {
			$order = explode( ',', $order );
			foreach ( $order as $k => $v ) {
				if ( in_array( $v, $posted ) ) { $tabs[] = $v; }
			}
		}

		return $tabs;
	} // End sanitize_tabs()

	/**
	 * Insert a tab grouping.
	 * @since  1.0.0
	 * @param  array $data Tab grouping data.
	 * @return bool
	 */
	private function insert_grouping ( $data ) {
		$response = 0;
		if ( ! isset( $data['title'] ) || '' == $data['title'] || ! isset( $data['slug'] ) || '' == $data['slug'] || ! isset( $data['tabs'] ) ) return $response;

		$tabs = $this->get_tab_groups();
		$tabs[] = $data;
		$response = update_option( $this->token . '-tab-groups', $tabs );

		return $response;
	} // End insert_grouping()

	/**
	 * Update a tab grouping.
	 * @since  1.0.0
	 * @param  array $data Tab grouping data.
	 * @return bool
	 */
	private function update_grouping ( $data ) {
		$response = 0;
		if ( ! isset( $data['title'] ) || ! isset( $data['slug'] ) || ! isset( $data['tabs'] ) ) { return $response; }

		$tabs = $this->get_tab_groups();

		if ( isset( $data['old-slug']) && $data['old-slug'] == $data['slug'] ) {
			unset( $data['old-slug']);

			foreach ( (array)$tabs as $k => $v ) {
				if ( strtolower( $v['slug'] ) == strtolower( $data['slug'] ) ) {
					$tabs[$k] = $data;
					break;
				}
			}
		} elseif( $data['old-slug'] != $data['slug'] ) {

			//Unset the old slug key
			foreach ( $tabs as $k => $value ) {
				if (  $tabs[ $k ]['slug'] == $data['old-slug'] )
					unset( $tabs[ $k ] );
			}

			unset( $data['old-slug']);

			$tabs[] = $data;
		}

		$response = update_option( $this->token . '-tab-groups', $tabs );
		return $response;
	} // End update_grouping()

	/**
	 * Delete a tab grouping.
	 * @since  1.0.0
	 * @param  array $data Tab grouping data.
	 * @return bool
	 */
	private function delete_grouping ( $slug ) {
		$response = 0;
		$do_update = 0;
		if ( empty( $slug ) ) return $response;

		$tabs = $this->get_tab_groups();
		foreach ( (array)$tabs as $k => $v ) {
			if ( isset( $v['slug'] ) && $v['slug'] == $slug ) {
				unset( $tabs[$k] );
				$do_update = 1;
			}
		}

		if ( $do_update == 1 ) $response = update_option( $this->token . '-tab-groups', $tabs );

		return $response;
	} // End delete_grouping()

	/**
	 * Check if a grouping exists, based on it's slug.
	 * @since  1.0.0
	 * @param  string $slug  The slug to test against.
	 * @return boolean       Whether or not the grouping exists.
	 */
	private function grouping_exists( $slug ) {
		$response = 0;

		if ( '' != $slug ) {
			$tabs = $this->get_tab_groups();

			foreach ( (array)$tabs as $k => $v ) {
				if ( isset( $v['slug'] ) && $v['slug'] == $slug ) {
					$response = 1;
					break;
				}
			}
		}

		return $response;
	} // End grouping_exists()

	/**
 	 * Filter the widget by tab group.
 	 * @param  {array} $instance the settings for the widget
 	 * @param  {object} $obj    the widget instance object
 	 * @param  {array} $args     arguments
 	 * @since 1.0.0
 	 * @return {array}           the instance
 	 */
 	public function filter_by_tab_group ( $instance, $obj, $args ) {
 		if ( 'woodojo_tabs' != $obj->id_base  ) return $instance;

 		if ( isset( $instance['woodojo_tab_group'] ) && ( '' != $instance['woodojo_tab_group'] ) ) {
 			$this->tab_group = esc_attr( $instance['woodojo_tab_group'] );
 		} else {
 			$this->tab_group = '';
 		}

 		return $instance;
 	} // End filter_by_tab_group()

 	/**
 	 * Apply the tab groupings filter to the tabs in the widget.
 	 * @since  1.0.0
 	 * @param  array $tabs Array of tabs to display in the widget.
 	 * @return array       Modified array of tabs.
 	 */
 	public function apply_tabs_filter ( $tabs ) {
 		if ( ! isset( $this->tab_group ) || $this->tab_group == '' ) { return $tabs; }

 		$allowed_tabs = $tabs;
 		$groups = $this->get_tab_groups();

 		if ( is_array( $groups ) && count( $groups ) > 0 ) {
 			$selected_tabs = array();

	 		foreach ( (array)$groups as $k => $v ) {
	 			if ( isset( $v['slug'] ) && ( $v['slug'] == $this->tab_group ) ) {
	 				$selected_tabs = (array)$v['tabs'];
	 				break;
	 			}
	 		}

	 		if ( count( $selected_tabs ) > 0 ) {
	 			$tabs = array();

	 			foreach ( $selected_tabs as $k => $v ) {
	 				if ( in_array( $v, array_keys( $allowed_tabs ) ) ) {
	 					$tabs[$v] = $allowed_tabs[$v];
	 				}
	 			}
	 		}
	 	}

 		return $tabs;
 	} // End apply_tabs_filter()

 	/**
 	 * Save the data from our custom form fields.
 	 * @param  array $instance array of settings for this widget
 	 * @param  array $new_instance array of settings for this widget
 	 * @param  array $old_instance array of settings for this widget
 	 * @param  object $obj      the instance of the widget
 	 * @since 1.0.0
 	 * @return array           array of settings for this widget
 	 */
 	public function save_widget_form ( $instance, $new_instance, $old_instance, $obj ) {
 		if ( isset( $new_instance['woodojo_tab_group'] ) ) {
 			$instance['woodojo_tab_group'] = $new_instance['woodojo_tab_group'];
 		} else {
 			$instance['woodojo_tab_group'] = false;
 		}
 		return $instance;
 	} // End save_widget_form()

 	/**
 	 * Output a checkbox on the widget control form.
 	 * @param  object $obj      the instance of the widget
 	 * @param  boolean $return   the return for the widget
 	 * @param  array $instance an array of settings for this widget
 	 * @since 1.0.0
 	 * @return void
 	 */
 	public function widget_form_html ( $obj, $return, $instance ) {
 		global $return;
 		if ( 'woodojo_tabs' != $obj->id_base  ) return $return;

 		if ( ! isset( $instance['woodojo_tab_group'] ) && '' == $instance['woodojo_tab_group']  ) {
 			$instance['woodojo_tab_group'] = false;
 		}

 		$tab_groups = $this->get_tab_groups();
?>
<!-- Widget Tab Group: Select Input -->
<p>
	<label for="<?php echo $obj->get_field_id( 'woodojo_tab_group' ); ?>"><?php _e( 'Tab Group', 'woodojo' ); ?></label>
	<select id="<?php echo $obj->get_field_id( 'woodojo_tab_group' ); ?>" name="<?php echo $obj->get_field_name( 'woodojo_tab_group' ); ?>" class="widefat">
		<option value=""><?php _e( 'All Tabs', 'woodojo' ); ?></option>
	<?php
		$html = '';
		foreach ( $tab_groups as $k => $v ) {
			$html .= '<option value="' . esc_attr( $v['slug'] ) . '" ' . selected( $instance['woodojo_tab_group'], $v['slug'], 0 ) . '>' . $v['title'] . '</option>' . "\n";
		}
		echo $html;
	?>
	</select>
</p>
<?php
 		$return = null;
 	} // End widget_form_html()
} // End Class
?>