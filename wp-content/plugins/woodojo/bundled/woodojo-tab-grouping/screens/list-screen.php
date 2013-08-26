<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
		<?php
		require_once( $this->classes_path . 'class-list-table.php' );
		$this->list_table = new WooDojo_Tab_Grouping_Table();
		$this->list_table->data = $this->get_tab_groups();
		$this->list_table->prepare_items();
		$this->list_table->display();
		?>
		</div><!--/.col-wrap-->
	</div><!--/#col-right-->
	<div id="col-left">
		<div class="form-wrap">
			<h3><?php _e( 'Add Tab Grouping', 'woodojo' ); ?></h3>
			<form id="addgrouping" method="post" action="" class="validate">
				<input type="hidden" name="action" value="add-tab-grouping" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->page_slug ); ?>" />
				<?php wp_nonce_field( $this->token . '-add-grouping', $this->token . '-add-grouping' ); ?>
				<div class="form-field form-required">
					<label for="grouping-name"><?php _e( 'Name', 'woodojo' ); ?></label>
					<input name="grouping-name" id="grouping-name" type="text" value="" size="40" aria-required="true" />
					<p><?php _e( 'The name is a reference for this group of tabs.', 'woodojo' ); ?></p>
				</div>
				<div class="form-field">
					<label for="grouping-slug"><?php _e( 'Slug', 'woodojo' ); ?></label>
					<input name="slug" id="grouping-slug" type="text" value="" size="40">
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
							$count++;
							$class = 'tab';
							if ( $count == count( $tabs ) ) { $class .= ' last'; }
							$html .= '<li class="' . esc_attr( $class ) . '"><label><input type="checkbox" name="tabs[]" value="' . esc_attr( $k ) . '" style="width: auto;" /> ' . $v . '</label></li>';
						}
						$html .= '</ul>' . "\n";

						$html .= '<input type="hidden" name="tab-order" value="' . esc_attr( join( ',', array_keys( $tabs ) ) ) . '" />' . "\n";
						echo $html;
					}
				?>
				</div><!--/.form-field-->
				<?php submit_button( __( 'Add Tab Group', 'woodojo' ), 'button' ); ?>
			</form>
		</div><!--/.form-wrap-->
	</div><!--/#col-left-->
</div><!--/#col-container-->