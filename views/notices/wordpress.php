<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$wp_status = wphc( 'module.server' )->is_updated( 'wp' );

if ( $wp_status === 'updated' || $wp_status === false ) {
	return false;
}

$messages = [
	'outdated' => [
		'class'   => 'notice-warning is-dismissible',
		'message' => __( 'A newer version of WordPress is available.', 'wp-healthcheck' ),
	],
	'obsolete' => [
		'class'   => 'notice-error is-dismissible',
		'message' => __( 'The WordPress version you are running is not supported anymore!', 'wp-healthcheck' ),
	],
];
?>

<div class="notice wphc-notice wphc-notice-wordpress <?php echo esc_attr( $messages[ $wp_status ]['class'] ); ?>">
	<p>
		<strong><?php esc_html_e( 'WP Healthcheck:', 'wp-healthcheck' ); ?></strong>
		<?php
		echo esc_html( $messages[ $wp_status ]['message'] ) . ' <a href="' . esc_url( admin_url( 'update-core.php' ) ) . '" aria-label="' . esc_attr__( 'Please update WordPress now', 'wp-healthcheck' ) . '">' . esc_html__( 'Please update it now', 'wp-healthcheck' ) . '</a>.';
		?>
	</p>
</div>
