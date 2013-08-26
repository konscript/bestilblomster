<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

global $woodojo;
?>
<div id="woodojo" class="wrap <?php echo esc_attr( $this->token ); ?>">
	<?php screen_icon( 'woodojo' ); ?>
	<h2><?php echo esc_html( $this->name ); ?></h2>
	<p class="powered-by-woo"><?php _e( 'Powered by', 'woodojo' ); ?><a href="http:www/woothemes.com" title="WooThemes"><img src="<?php echo $woodojo->base->assets_url; ?>images/woothemes.png" alt="WooThemes" /></a></p>
	
	<form action="options.php" method="post">
		<?php $this->settings_tabs(); ?>
		<?php settings_fields( $this->token ); ?>
		<?php do_settings_sections( $this->token ); ?>
		<?php submit_button(); ?>
	</form>
</div><!--/#woodojo-->