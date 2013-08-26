<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

global $woodojo;
if ( $this->model->current_action_response == 'cred' ) {
	return;
}
?>
<div id="woodojo" class="wrap">
	<div id="icon-dojo" class="icon32"><br></div>
	<h2><?php echo esc_html( $this->name ); ?> <span class="version"><?php echo esc_html( $woodojo->version ); ?></span></h2>
	<p class="powered-by-woo"><?php _e( 'Powered by', 'woodojo' ); ?><a href="http://www.woothemes.com" title="WooThemes"><img src="<?php echo $this->assets_url; ?>images/woothemes.png" alt="WooThemes" /></a></p>
	<p><?php _e( 'WooDojo is a powerful toolkit of features to enhance your website. Select only the functionality that you need, without unnecessary code. Enhance your website with WooDojo, today.', 'woodojo' ); ?></p>
	<ul class="subsubsub">
		<?php echo $this->model->get_section_links(); ?>
	</ul>
	<ul class="subsubsub fr hide-if-no-js open-close-all">
		<li><a href="#open-all"><?php _e( 'Open All', 'woodojo' ); ?></a></li>
		<li><a href="#close-all"><?php _e( 'Close All', 'woodojo' ); ?></a></li>
	</ul>
	
	<br class="clear"/>
	
	<?php
		foreach ( $this->model->components as $k => $v ) {
			if ( count( $v ) > 0 ) {
				include( $this->screens_path . 'main/section.php' );
			}
		}
	?>
	<br class="clear" />
</div><!--/#woodojo .wrap-->