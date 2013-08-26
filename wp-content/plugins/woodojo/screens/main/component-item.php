<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

	$css_class = '';

	if ( in_array( $i, $this->model->closed_components ) ) {
		$css_class .= ' closed';
	}

	$version = '';
	if ( isset( $this->model->components[$k][$i]->current_version ) ) {
		$version = $this->model->components[$k][$i]->current_version;
	}
	if ( $version == '' && isset( $this->model->components[$k][$i]->version ) ) {
		$version = $this->model->components[$k][$i]->version;
	}

	$has_upgrade = false;
	if ( $k == 'downloadable' && ( $this->model->components[$k][$i]->is_free ) ) {
		if ( isset( $this->model->components[$k][$i]->has_upgrade ) && $this->model->components[$k][$i]->has_upgrade != false ) {
			$has_upgrade = true;
			$css_class .= ' has-upgrade';
		}
	}
?>
<div id="<?php echo esc_attr( $i ); ?>" class="widget <?php echo esc_attr( $this->model->get_status_token( $i, $k ) ) . $css_class; ?>">
	<div class="widget-top">
		<div class="widget-title-action">
		<a class="widget-action hide-if-no-js" href="#close-component"></a>
	</div>
	    <div class="widget-title">
	    	<h4>
	    		<span class="status-label"><?php echo $this->model->get_status_label( $i, $k ); ?></span>
	    		<span class="title"><?php echo esc_html( $j->title ); ?></span>
	    		<?php if ( $version != '' ) { ?>
	    		<span class="version">
	    			<?php echo $version; ?>
	    			<?php
	    				if ( $has_upgrade != false ) {
	    			?>
	    			 - <a href="<?php echo esc_url( $this->model->get_upgrade_link_url( $i, $k ) ); ?>" class="upgrade-link"><?php printf( __( 'Update to v%s', 'woodojo' ), $this->model->components[$k][$i]->version ); ?></a>
	    			<?php
	    				}
	    			?>
	    		</span>
	    		<?php } ?>
	    		<span class="in-widget-title"></span>
	    	</h4>
	    </div>
	</div>
	<div class="module-inside">
	    <div class="info">
	    	<img src="<?php echo esc_url( $this->model->get_screenshot_url( $i, $k ) ); ?>" alt="thumb" />
	    	<p>
	    	<?php
	    		if ( isset( $j->short_description ) ) {
	    			echo esc_html( $j->short_description );
	    		}

	    		if ( $k != 'bundled' ) {
	    	?>
	    	<a id="<?php echo esc_attr( $i ); ?>-info" href="<?php echo esc_url( admin_url( 'admin.php?page=woodojo&screen=more-information&component=' . esc_attr( $i ) . '&type=' . esc_attr( $k ) . '&KeepThis=true&TB_iframe=true' ) ); ?>" class="thickbox" title="<?php esc_attr_e( 'More Information', 'woodojo' ); ?>"><?php _e( 'More Information &rarr;', 'woodojo' ); ?></a>
	    	<?php } ?>
	    	</p>
	    	<div class="actions">
	    		<form method="post" name="component-actions" action="" >
	    		<?php wp_nonce_field( $i ); ?>
	    		<div>
	    			<?php
	    				echo $this->model->get_action_button( $i, $k );

	    				if ( isset( $this->model->components[$k][$i]->settings ) && $this->model->components[$k][$i]->settings != '' ) {
	    					$class = '';
	    					if ( $this->model->get_status_token( $i, $k ) == 'disabled' ) {
	    						$class = ' hidden';
	    					}
	    					echo '<span class="settings-link' . $class . '"> | <a href="' . admin_url( 'admin.php?page=' . urlencode( $this->model->components[$k][$i]->settings ) ) . '">' . __( 'Settings', 'woodojo' ) . '</a></span>' . "\n";
	    				}
 	    			?>
	    			<input type="hidden" name="component-type" value="<?php echo esc_attr( $k ); ?>" />
	    			<input type="hidden" name="component-path" value="<?php echo esc_attr( $j->filepath ); ?>" />
	    			<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" class="ajax-loading" id="ajax-loading" alt="<?php esc_attr_e( 'Loading', 'woodojo' ); ?>" />
	    		</div>
	    		<div class="clear"></div>
	    		</form>
	    	</div>
	    	
	    	<div class="clear"></div>
	    </div>
	</div>		
</div>