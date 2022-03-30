<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

if ( ! wphc()->ajax->is_doing_ajax() || ! isset( $options ) ) {
	return;
}

$fail = array_filter(
	$options,
	function( $k ) {
		return false === $k;
	}
);

$success = array_filter(
	$options,
	function( $k ) {
		return false !== $k;
	}
);

$message = ( isset( $reactivate ) && $reactivate ) ? __( 'reactivate', 'wp-healthcheck' ) : __( 'deactivate', 'wp-healthcheck' );

$message_singular = ( isset( $reactivate ) && $reactivate ) ? _x( 'reactivated', 'singular: option was reactivated', 'wp-healthcheck' ) : _x( 'deactivated', 'singular: option was disabled', 'wp-healthcheck' );
$message_plural   = ( isset( $reactivate ) && $reactivate ) ? _x( 'reactivated', 'plural: options were reactivated', 'wp-healthcheck' ) : _x( 'deactivated', 'plural: options were disabled', 'wp-healthcheck' );
?>

<?php if ( sizeof( $success ) == 1 ) : ?>
	<p class="wphc_autoload_status wphc_success">
		<?php
		/* translators: %1$s is the option name, %2$s is the status ('deactivated' or 'reactivated') */
		echo sprintf( __( 'Yay, the <strong>%1$s</strong> option was %2$s successfully.', 'wp-healthcheck' ), key( $success ), $message_singular );
		?>
	</p>
<?php elseif ( sizeof( $success ) > 1 ) : ?>
	<p class="wphc_autoload_status wphc_success">
		<?php
		/* translators: %1$s is the status ('deactivated' or 'reactivated') */
		echo sprintf( __( 'Yay, the below options were %1$s successfully:', 'wp-healthcheck' ), $message_plural );
		?>

		<br/><br/>

		<?php foreach ( $success as $name => $value ) : ?>
			- <strong><?php echo $name; ?></strong><br/>
		<?php endforeach; ?>
	</p>
<?php endif; ?>

<?php if ( sizeof( $fail ) > 0 ) : ?>
	<?php foreach ( $fail as $name => $value ) : ?>
		<p class="wphc_autoload_status wphc_error">
			<?php
			/* translators: %1$s is the status ('deactivate' or 'reactivate'), %2$s is the option name */
			echo sprintf( __( 'Oops, for some reason we couldn\'t %1$s the <strong>%2$s</strong> option.', 'wp-healthcheck' ), $message, $name );
			?>
		</p>
	<?php endforeach; ?>
<?php endif; ?>
