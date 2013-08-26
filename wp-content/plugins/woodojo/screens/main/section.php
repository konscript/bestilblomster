<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}
?>
<div id="<?php echo esc_attr( $this->token . '-' . $k ); ?>" class="widgets-holder-wrap">
		<h3 class="section-title"><?php echo esc_html( $this->model->sections[$k]['name'] ); ?> <span id="removing-widget"><?php _e( 'Deactivate', 'woodojo' ); ?><span></span></span></h3>
		<?php if ( isset( $this->model->sections[$k]['description'] ) ) { ?><p class="description"><?php echo esc_html( $this->model->sections[$k]['description'] ); ?></p><?php } ?>
		<div id="module-list">
		<div class="clear"></div>
		<?php
			$count = 0;
			foreach ( $v as $i => $j ) {
				$count++;
				include( $this->model->config->screens_path . 'main/component-item.php' );
				if( 3 == $count ) {
					echo('<br class="clear" />');
					$count = 0;
				}
			}
		?>
		<div class="clear"></div>
		</div>
	<br class="clear" />
</div><!--/#modules-->