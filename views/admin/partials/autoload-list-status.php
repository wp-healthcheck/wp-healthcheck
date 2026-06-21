<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

if ( ! wphc( 'admin.ajax' )->is_doing_ajax() || ! isset( $options ) ) {
	return;
}

$fail = array_filter(
	$options,
	function ( $k ) {
		return $k === false;
	}
);

$success = array_filter(
	$options,
	function ( $k ) {
		return $k !== false;
	}
);

$message = ( isset( $reactivate ) && $reactivate ) ? __( 'reactivate', 'wp-healthcheck' ) : __( 'deactivate', 'wp-healthcheck' );

$message_singular = ( isset( $reactivate ) && $reactivate ) ? _x( 'reactivated', 'singular: option was reactivated', 'wp-healthcheck' ) : _x( 'deactivated', 'singular: option was disabled', 'wp-healthcheck' );
$message_plural   = ( isset( $reactivate ) && $reactivate ) ? _x( 'reactivated', 'plural: options were reactivated', 'wp-healthcheck' ) : _x( 'deactivated', 'plural: options were disabled', 'wp-healthcheck' );
?>

<?php if ( count( $success ) === 1 ) : ?>
	<p class="wphc_autoload_status wphc_success">
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s is the option name, %2$s is the status ('deactivated' or 'reactivated'). */
				__( 'Yay, the <strong>%1$s</strong> option was %2$s successfully.', 'wp-healthcheck' ),
				esc_html( key( $success ) ),
				esc_html( $message_singular )
			),
			[ 'strong' => [] ]
		);
		?>
	</p>
<?php elseif ( count( $success ) > 1 ) : ?>
	<p class="wphc_autoload_status wphc_success">
		<?php
		/* translators: %1$s is the status ('deactivated' or 'reactivated'). */
		echo esc_html( sprintf( __( 'Yay, the below options were %1$s successfully:', 'wp-healthcheck' ), $message_plural ) );
		?>

		<br/><br/>

		<?php foreach ( $success as $name => $value ) : ?>
			- <strong><?php echo esc_html( $name ); ?></strong><br/>
		<?php endforeach; ?>
	</p>
<?php endif; ?>

<?php if ( count( $fail ) > 0 ) : ?>
	<?php foreach ( $fail as $name => $value ) : ?>
		<p class="wphc_autoload_status wphc_error">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s is the status ('deactivate' or 'reactivate'), %2$s is the option name. */
					__( 'Oops, for some reason we couldn\'t %1$s the <strong>%2$s</strong> option.', 'wp-healthcheck' ),
					esc_html( $message ),
					esc_html( $name )
				),
				[ 'strong' => [] ]
			);
			?>
		</p>
	<?php endforeach; ?>
<?php endif; ?>
