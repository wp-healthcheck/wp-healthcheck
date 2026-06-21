<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$opts = wphc( 'module.autoload' )->get();
?>

<form id="wphc-autoload-form" name="wphc-autoload-form">
	<div class="wphc_autoload_list">
		<ul>
			<li></li>
			<li><?php esc_html_e( 'Name', 'wp-healthcheck' ); ?></li>
			<li><?php esc_html_e( 'Size', 'wp-healthcheck' ); ?></li>
		</ul>

		<?php foreach ( $opts as $name => $size ) : ?>
			<?php
			$opt_title = '';
			$opt_id    = 'wphc-opt-' . rawurlencode( $name );
			$is_core   = wphc( 'module.autoload' )->is_core_option( $name );

			if ( $is_core ) {
				$opt_title = __( 'You can\'t deactivate a WordPress core option.', 'wp-healthcheck' );
			}
			?>

			<ul>
				<?php if ( ! $is_core ) : ?>
					<li><input name="<?php echo esc_attr( $opt_id ); ?>" id="<?php echo esc_attr( $opt_id ); ?>" type="checkbox" />&nbsp;</li>
				<?php else : ?>
					<li><input type="checkbox" disabled="disabled" title="<?php echo esc_attr( $opt_title ); ?>" /></li>
				<?php endif; ?>

				<li class="wphc-col-name"><label for="<?php echo esc_attr( $opt_id ); ?>" title="<?php echo esc_attr( $opt_title ); ?>"><?php echo esc_html( $name ); ?></label></li>
				<li><label for="<?php echo esc_attr( $opt_id ); ?>" title="<?php echo esc_attr( $opt_title ); ?>"><?php echo esc_html( number_format( $size, 2 ) ); ?>MB</label></li>
			</ul>
		<?php endforeach; ?>
	</div>
</form>

<div>
	<button type="submit" class="button button-small button-primary" form="wphc-autoload-form"><?php esc_html_e( 'Deactivate Selected Options', 'wp-healthcheck' ); ?></button>

	<button type="submit" class="button button-small" id="wphc-btn-autoload-close"><?php esc_html_e( 'Close', 'wp-healthcheck' ); ?></button></div>
</div>
