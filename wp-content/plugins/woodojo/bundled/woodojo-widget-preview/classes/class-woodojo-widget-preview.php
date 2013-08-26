<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooDojo Widget Preview Mode Class
 *
 * All functionality pertaining to the widget preview mode downloadable component.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Downloadables
 * @author Matty
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $capability
 *
 * - __construct()
 * - control_widget_access()
 * - save_widget_form()
 * - widget_form_html()
 */
class WooDojo_Widget_Preview {
	var $capability;

	/**
	 * Constructor.
	 * @since 1.0.0
	 * @return void
	 */
 	public function __construct () {
 		$this->capability = 'manage_options';
 		if ( is_admin() ) {
	 		add_filter( 'widget_update_callback', array( &$this, 'save_widget_form' ), 10, 2 );
	 		add_action( 'in_widget_form', array( &$this, 'widget_form_html' ), 10, 3 );
 		} else {
 			add_filter( 'widget_display_callback', array( &$this, 'control_widget_access' ), 10, 3 );
 		}
 	} // End __construct()

 	/**
 	 * Control the display of a widget.
 	 * @param  {array} $instance the settings for the widget
 	 * @param  {object} $obj    the widget instance object
 	 * @param  {array} $args     arguments
 	 * @since 1.0.0
 	 * @return {array}           the instance
 	 */
 	public function control_widget_access ( $instance, $obj, $args ) {
 		if ( isset( $instance['widget_preview_mode'] ) && ( $instance['widget_preview_mode'] == true ) && ( ! current_user_can( $this->capability ) ) ) {
 			return false;
 		}

 		return $instance;
 	} // End control_widget_access()

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
 		if ( isset( $new_instance['widget_preview_mode'] ) ) {
 			$instance['widget_preview_mode'] = $new_instance['widget_preview_mode'];
 		} else {
 			$instance['widget_preview_mode'] = false;
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

 		if ( ! isset( $instance['widget_preview_mode'] ) ) {
 			$instance['widget_preview_mode'] = false;
 		}
?>
<!-- Widget Preview: Checkbox Input -->
<p>
	<input id="<?php echo $obj->get_field_id( 'widget_preview_mode' ); ?>" name="<?php echo $obj->get_field_name( 'widget_preview_mode' ); ?>" type="checkbox"<?php checked( $instance['widget_preview_mode'], 1 ); ?> value="1" />
	<label for="<?php echo $obj->get_field_id( 'widget_preview_mode' ); ?>"><?php _e( 'Preview Mode', 'woodojo' ); ?></label>
</p>
<?php
 		$return = null;
 	} // End widget_form_html()
 } // End Class
?>