<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}
?>
<body class="iframe">

<div id="woodojo" class="wrap more-information">

	<h2 class="main-heading"><?php echo esc_html( $this->component->title ); ?></h2>
	
	<h3><?php _e( 'Description', 'woodojo' ); ?></h3>
	
	<p><?php echo $this->component->long_description; ?></p>

<?php if ( count( $this->screenshots ) > 0 ) { ?>
	<div id="images">
		<h3><?php _e( 'Screenshots', 'woodojo' ); ?></h3>
		<div class="flexslider">
		    <ul class="slides">
		    	<?php foreach ( $this->screenshots as $k => $v ) { ?>
		    	<li><img src="<?php echo $v; ?>" /></li>
		    	<?php } ?>
		    </ul>
		</div>	
	</div>
<?php } ?>	
	<br class="clear"/>
	
</div><!--/#woodojo .wrap-->

</body>
</html>