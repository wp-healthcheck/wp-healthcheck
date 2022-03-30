<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$status = wphc()->main->is_software_updated( 'wp' );

if ( 'updated' == $status || false === $status ) {
	return false;
}

$messages = array(
	'outdated' => array(
		'class'   => 'notice-warning is-dismissible',
		'message' => __( 'A newer version of WordPress is available.', 'wp-healthcheck' ),
	),
	'obsolete' => array(
		'class'   => 'notice-error is-dismissible',
		'message' => __( 'The WordPress version you are running is not supported anymore!', 'wp-healthcheck' ),
	),
);
?>

<div class="notice wphc-notice wphc-notice-wordpress <?php echo $messages[ $status ]['class']; ?>">
	<p>
		<strong>WP Healthcheck:</strong>
		<?php echo $messages[ $status ]['message'] . ' <a href="' . site_url() . '/wp-admin/update-core.php" aria-label="' . __( 'Please update WordPress now', 'wp-healthcheck' ) . '">' . __( 'Please update it now', 'wp-healthcheck' ) . '</a>.'; ?>
	</p>
</div>
