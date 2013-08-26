<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo - Custom Code Settings
 *
 * Settings for the WooDojo - Custom Code feature.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Bundled
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * 
 * - __construct()
 * - init_sections()
 * - init_fields()
 * - validate_field_css()
 * - validate_field_html()
 * - get_allowed_html_tags()
 */
class WooDojo_CustomCode_Settings extends WooDojo_Settings_API {
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct () {
		global $woodojo;
	    parent::__construct(); // Required in extended classes.
	} // End __construct()

	/**
	 * init_sections function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init_sections () {
	    $sections = array();
	    
	    $sections['custom-css'] = array(
	    						'name' => __( 'Custom CSS', 'woodojo' ), 
	    						'description' => __( 'Add custom CSS code to your website.', 'woodojo' )
	    						);

	    if ( current_user_can( 'unfiltered_html' ) ) {
		    $sections['custom-html'] = array(
		    						'name' => __( 'Custom HTML', 'woodojo' ), 
		    						'description' => __( 'Add custom HTML code to the &lt;head&gt; section or before the closing &lt;/body&gt; tag on your website.', 'woodojo' )
	    							);
		}
	    
	    $this->sections = $sections;
	} // End init_sections()
	
	/**
	 * init_fields function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init_fields () {
	    $fields = array();
	    
	    $fields['custom-css-enable'] = array(
	    						'name' => __( 'Enable Custom CSS', 'woodojo' ), 
	    						'description' => __( 'Output the custom CSS code on your website.', 'woodojo' ), 
	    						'type' => 'checkbox', 
	    						'default' => '', 
	    						'section' => 'custom-css', 
	    						'required' => 0
	    						);

	    $fields['custom-css-code'] = array(
	    						'name' => __( 'Custom CSS Code', 'woodojo' ), 
	    						'description' => __( 'Output this custom CSS code on your website.', 'woodojo' ), 
	    						'type' => 'css', 
	    						'default' => '', 
	    						'section' => 'custom-css', 
	    						'required' => 0, 
	    						'form' => 'form_field_textarea', 
	    						'validate' => 'validate_field_css' 
	    						);

	    if ( current_user_can( 'unfiltered_html' ) ) {
		    $fields['custom-html-enable'] = array(
		    						'name' => __( 'Enable Custom HTML', 'woodojo' ), 
		    						'description' => __( 'Output the custom HTML code on your website.', 'woodojo' ), 
		    						'type' => 'checkbox', 
		    						'default' => '', 
		    						'section' => 'custom-html', 
		    						'required' => 0
		    						);

		    $fields['custom-html-code-head'] = array(
		    						'name' => __( 'Inside the &lt;head&gt; Tags', 'woodojo' ), 
		    						'description' => __( 'Output custom HTML code inside the &lt;head&gt; tags of your website (JavaScript is not permitted).', 'woodojo' ), 
		    						'type' => 'html', 
		    						'default' => '', 
		    						'section' => 'custom-html', 
		    						'required' => 0, 
		    						'form' => 'form_field_textarea', 
		    						'validate' => 'validate_field_html' 
		    						);

		    $fields['custom-html-code-footer'] = array(
		    						'name' => __( 'Before the closing &lt;/body&gt; Tag', 'woodojo' ), 
		    						'description' => __( 'Output custom HTML code before the closing &lt;/body&gt; tag of your website (JavaScript is not permitted).', 'woodojo' ), 
		    						'type' => 'html', 
		    						'default' => '', 
		    						'section' => 'custom-html', 
		    						'required' => 0, 
		    						'form' => 'form_field_textarea', 
		    						'validate' => 'validate_field_html' 
		    						);  
		}

	    $this->fields = $fields;
	} // End init_fields()

	/**
	 * form_field_textarea function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param array $args
	 * @return void
	 */
	public function form_field_textarea ( $args ) {
		if ( ! current_user_can( 'unfiltered_html' ) ) { return; }

		$options = $this->get_settings();

		echo '<textarea id="' . esc_attr( $args['key'] ) . '" name="' . esc_attr( $this->token ) . '[' . esc_attr( $args['key'] ) . ']" cols="42" rows="5">' . esc_attr( $options[ esc_attr( $args['key'] ) ] ) . '</textarea>' . "\n";
		if ( isset( $args['data']['description'] ) ) {
			echo '<p><span class="description">' . esc_attr( $args['data']['description'] ) . '</span></p>' . "\n";
		}
	} // End form_field_textarea()

	/**
	 * validate_field_css function.
	 * 
	 * @access public
	 * @param string $input
	 * @since 1.0.0
	 * @return void
	 */
	public function validate_field_css ( $input ) {
		$input = wp_filter_nohtml_kses( strip_tags( $input ) );

		return $input;
	} // End validate_field_css()

	/**
	 * validate_field_html function.
	 * 
	 * @access public
	 * @param string $input
	 * @since 1.0.0
	 * @return void
	 */
	public function validate_field_html ( $input ) {
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$input = wp_strip_all_tags( $input );
		}

		$input = wp_kses( $input, $this->get_allowed_html_tags() );
		$input = esc_textarea( $input );

		return $input;
	} // End validate_field_html()

	/**
	 * Return an array of HTML tags allowed in the "Custom HTML" fields.
	 * @since  1.0.2
	 * @return array The allowed HTML tags.
	 */
	private function get_allowed_html_tags () {
		return array(
			'p' => array(
				'class' => array(),
				'align' => array(),
				'dir' => array(),
				'lang' => array(),
				'style' => array(),
				'xml:lang' => array()),
			'a' => array(
				'href' => array (),
				'title' => array ()),
			'abbr' => array(
				'title' => array ()),
			'acronym' => array(
				'title' => array ()),
			'b' => array(),
			'blockquote' => array(
				'cite' => array ()),
			'cite' => array (),
			'code' => array(),
			'del' => array(
				'datetime' => array ()),
			'em' => array (), 'i' => array (),
			'q' => array(
				'cite' => array ()),
			'strike' => array(),
			'strong' => array(), 
			'meta' => array( 'name' => array(), 'content' => array() ), 
			'div' => array( 'class' => array(), 'id' => array(), 'style' => array() )
		);
	} // End get_allowed_html_tags()
} // End Class
?>