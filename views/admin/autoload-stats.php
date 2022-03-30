<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$autoload = wphc()->main->get_autoload_stats();
$history  = wphc()->main->get_autoload_history();
?>

<div class="wphc_stats">
	<ul>
		<li><?php _e( 'Total:', 'wp-healthcheck' ); ?></li>
		<li><?php echo $autoload['count']; ?></li>
	</ul>
	<ul>
		<li><?php _e( 'Size:', 'wp-healthcheck' ); ?></li>
		<li><?php echo number_format( $autoload['size'], 2 ); ?>MB</li>
	</ul>
</div>

<div>
	<button type="button" class="button" id="wphc-btn-autoload-list">
		<?php _e( 'Top Autoload Options', 'wp-healthcheck' ); ?>
	</button>

	<?php if ( is_array( $history ) && sizeof( $history ) > 0 ) : ?>
		<button type="button" class="button" id="wphc-btn-autoload-history">
			<?php _e( 'History', 'wp-healthcheck' ); ?>
		</button>
	<?php endif; ?>

	<button class="wphc_help_btn button dashicons dashicons-editor-help" id="wphc-btn-autoload-help"><span class="screen-reader-text"><?php _e( 'Help: Autoload Options', 'wp-healthcheck' ); ?></span></button>
</div>

<div id="wphc-autoload-list"></div>
