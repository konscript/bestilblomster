<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooDojo HTML Term Description Class
 *
 * Allow HTML in term descriptions and show a WYSIWYG editor.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Downloadable
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $service
 * var $token
 * var $settings_sreen
 * var $settings
 *
 * - __construct()
 * - queue_render_actions()
 * - render_field_edit()
 * - render_field_add()
 */
class WooDojo_HTML_Term_Description {
	/* Variable Declarations */
	public $token;
	
	/**
	 * Constructor.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct () {
		if ( ! current_user_can( 'unfiltered_html' ) ) { return; } // Only users with the "unfiltered_html" capability can use this feature.

		/* Class Settings */
		$this->token = 'woodojo';

		/* Allow HTML */
		remove_filter( 'pre_term_description', 'wp_filter_kses' );
		remove_filter( 'term_description', 'wp_kses_data' );

		/* Queue the render actions in admin_init. */
		add_action( 'admin_init', array( &$this, 'queue_render_actions' ) );
	} // End __construct()
	
	/**
	 * Queue the render actions.
	 * @access  public
	 * @since   1.0.1
	 * @return  void
	 */
	public function queue_render_actions () {
		/* Add the tinymce powered field */
		$taxonomies = get_taxonomies('','names'); 
		foreach ( $taxonomies as $taxonomy ) {
			add_action( $taxonomy . '_edit_form_fields', array( &$this, 'render_field_edit' ), 1, 2 );
			add_action( $taxonomy . '_add_form_fields', array( &$this, 'render_field_add' ), 1, 1 );
		}
	} // End queue_render_actions()

	/**
	 * Add the WYSIWYG editor to the "edit" field.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function render_field_edit ( $tag, $taxonomy ) {
		
		$settings = array(
			'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
			'textarea_name'	=> 'description',
			'quicktags' 	=> true,
			'tinymce' 		=> true,
			'editor_css'	=> '<style>#wp-html-description-editor-container .wp-editor-area{ height:250px; }</style>'
			);
		
		?>
		<tr>
			<th scope="row" valign="top"><label for="description"><?php _ex( 'Description', 'Taxonomy Description' ); ?></label></th>
			<td><?php wp_editor( htmlspecialchars_decode( $tag->description ), 'html-description', $settings ); ?>
			<span class="description"><?php _e( 'The description is not prominent by default, however some themes may show it.', 'woodojo' ); ?></span></td>
			<script type="text/javascript">
				// Remove the non-html field
				jQuery( 'textarea#description' ).closest( '.form-field' ).remove();
			</script>
		</tr>
		<?php	
	} // End render_field_edit()

	/**
	 * Add the WYSIWYG editor to the "add" field.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function render_field_add ( $taxonomy ) {
		
		$settings = array(
			'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
			'textarea_name'	=> 'description',
			'quicktags' 	=> true,
			'tinymce' 		=> true,
			'editor_css'	=> '<style>#wp-html-tag-description-editor-container .wp-editor-area{ height:150px; }</style>'
			);
		
		?>
		<div>
			<label for="tag-description"><?php _ex( 'Description', 'Taxonomy Description', 'woodojo' ); ?></label>
			<?php wp_editor( '', 'html-tag-description', $settings ); ?>
			<p class="description"><?php _e( 'The description is not prominent by default, however some themes may show it.', 'woodojo' ); ?></p>
			<script type="text/javascript">
				// Remove the non-html field
				jQuery( 'textarea#tag-description' ).closest( '.form-field' ).remove();
				
				jQuery(function() {
					// Trigger save
					jQuery( '#addtag' ).on( 'mousedown', '#submit', function() {
				   		tinyMCE.triggerSave();
				    }); 
			    });
			    
			</script>
		</div>
		<?php	
	} // End render_field_add()	
} // End Class
