<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$opts = wphc( 'module.autoload' )->get_history();

if ( sizeof( $opts ) == 0 ) {
	return;
}
?>

<form id="wphc-autoload-form" name="wphc-autoload-form">
	<div class="wphc_autoload_list">
		<ul>
			<li></li>
			<li><?php _e( 'Name', 'wp-healthcheck' ); ?></li>
			<li><?php _e( 'Deactivation Time', 'wp-healthcheck' ); ?></li>
		</ul>

		<?php foreach ( $opts as $name => $timestamp ) : ?>
			<?php if ( get_option( $name ) ) : ?>
				<ul>
					<li><input name="wphc-hopt-<?php echo $name; ?>" id="wphc-hopt-<?php echo $name; ?>" type="checkbox" />&nbsp;</li>
					<li class="wphc-col-name"><label for="wphc-hopt-<?php echo $name; ?>"><?php echo $name; ?></label></li>
					<li><label for="wphc-hopt-<?php echo $name; ?>"><?php echo date( 'Y-m-d H:i:s', $timestamp ); ?></label></li>
				</ul>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<input name="wphc-history" id="wphc-history" value="1" type="hidden" />
</form>

<div>
	<button type="submit" class="button button-small button-primary" form="wphc-autoload-form"><?php _e( 'Reactivate Selected Options', 'wp-healthcheck' ); ?></button>

	<button type="submit" class="button button-small" id="wphc-btn-autoload-close"><?php _e( 'Close', 'wp-healthcheck' ); ?></button></div>
</div>
