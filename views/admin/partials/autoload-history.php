<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$opts = wphc( 'module.autoload' )->get_history();

if ( count( $opts ) === 0 ) {
	return;
}
?>

<form id="wphc-autoload-form" name="wphc-autoload-form">
	<div class="wphc_autoload_list">
		<ul>
			<li></li>
			<li><?php esc_html_e( 'Name', 'wp-healthcheck' ); ?></li>
			<li><?php esc_html_e( 'Deactivation Time', 'wp-healthcheck' ); ?></li>
		</ul>

		<?php foreach ( $opts as $name => $timestamp ) : ?>
			<?php if ( get_option( $name ) ) : ?>
				<ul>
					<li><input name="wphc-hopt-<?php echo esc_attr( $name ); ?>" id="wphc-hopt-<?php echo esc_attr( $name ); ?>" type="checkbox" />&nbsp;</li>
					<li class="wphc-col-name"><label for="wphc-hopt-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></label></li>
					<li><label for="wphc-hopt-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( gmdate( 'Y-m-d H:i:s', $timestamp ) ); ?></label></li>
				</ul>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<input name="wphc-history" id="wphc-history" value="1" type="hidden" />
</form>

<div>
	<button type="submit" class="button button-small button-primary" form="wphc-autoload-form"><?php esc_html_e( 'Reactivate Selected Options', 'wp-healthcheck' ); ?></button>

	<button type="submit" class="button button-small" id="wphc-btn-autoload-close"><?php esc_html_e( 'Close', 'wp-healthcheck' ); ?></button></div>
</div>
