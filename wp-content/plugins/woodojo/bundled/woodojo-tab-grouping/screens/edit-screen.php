<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

check_admin_referer( $this->token . '-edit-screen', '_wpnonce' );

if ( ! current_user_can( 'manage_options' ) )
	wp_die( __( 'Cheatin&#8217; uh?' ) );

$slug = sanitize_title_with_dashes( esc_attr( strip_tags( $_GET['slug'] ) ) );

$groups = $this->get_tab_groups();

$tab_data = array( 'title' => '', 'slug' => '', 'tabs' => array() );

foreach ( (array)$groups as $k => $v ) {
	if ( isset( $v['slug'] ) && ( $v['slug'] == $slug ) ) {
		$tab_data = $v;
		break;
	}
}

// Make sure to preserve the existing tab order, with non-checked tabs at the end.
// Uses the $tabs array created inside the object.
$ordered_tabs = array();
$unordered_tabs = array();

foreach ( $tab_data['tabs'] as $k => $v ) {
	if ( in_array( $v, array_keys( $tabs ) ) ) {
		$ordered_tabs[$v] = $tabs[$v];
	}
}

// Add the unchecked tabs as well.
foreach ( $tabs as $k => $v ) {
	if ( ! in_array( $k, array_keys( $ordered_tabs ) ) ) {
		$unordered_tabs[$k] = $v;
	}
}

$tabs = array_merge( $ordered_tabs, $unordered_tabs );
?>
<div id="col-container">
	<div id="col-left">
		<div class="form-wrap">
			<h3><?php _e( 'Edit Tab Grouping', 'woodojo' ); ?></h3>
			<form id="editgrouping" method="post" action="" class="validate">
				<input type="hidden" name="action" value="edit-tab-grouping" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->page_slug ); ?>" />
				<?php wp_nonce_field( $this->token . '-edit-tab-grouping', $this->token . '-edit-tab-grouping' ); ?>
				<div class="form-field form-required">
					<label for="grouping-name"><?php _e( 'Name', 'woodojo' ); ?></label>
					<input name="grouping-name" id="grouping-name" type="text" value="<?php echo esc_attr( $tab_data['title'] ); ?>" size="40" aria-required="true" />
					<p><?php _e( 'The name is a reference for this group of tabs.', 'woodojo' ); ?></p>
				</div>
				<div class="form-field">
					<label for="grouping-slug"><?php _e( 'Slug', 'woodojo' ); ?></label>
					<input name="slug" id="grouping-slug" type="text" value="<?php echo esc_attr( $tab_data['slug'] ); ?>" size="40" />
					<input name="old-slug" id="old-grouping-slug" type="hidden" value="<?php echo esc_attr( $tab_data['slug'] ); ?>" />
					<p><?php _e( 'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens. This value must be unique.', 'woodojo' ); ?></p>
				</div>
				<div class="form-field">
				<label for="tabs"><?php _e( 'Tabs', 'woodojo' ); ?></label>
				<?php
					if ( count( (array)$tabs ) <= 0 ) {
						echo '<p>' . __( 'No tabs are currently defined.', 'woodojo' ) . '</p>';
					} else {
						$html = '';
						$html .= '<ul class="sortable-tab-list">' . "\n";
						$count = 0;
						foreach ( $tabs as $k => $v ) {
							$checked = '';
							if ( in_array( $k, (array)$tab_data['tabs'] ) ) { $checked = ' checked="checked"'; }

							$count++;
							$class = 'tab';
							if ( $count == count( $tabs ) ) { $class .= ' last'; }
							$html .= '<li class="' . esc_attr( $class ) . '"><label><input type="checkbox" name="tabs[]" value="' . esc_attr( $k ) . '" style="width: auto;"' . $checked . ' /> ' . $v . '</label></li>';
						}
						$html .= '</ul>' . "\n";

						$html .= '<input type="hidden" name="tab-order" value="' . esc_attr( join( ',', array_keys( $tabs ) ) ) . '" />' . "\n";
						echo $html;
					}
				?>
				</div><!--/.form-field-->
				<?php submit_button( __( 'Edit Tab Group', 'woodojo' ), 'button' ); ?>
			</form>
		</div><!--/.form-wrap-->
	</div><!--/#col-left-->
</div><!--/#col-container-->