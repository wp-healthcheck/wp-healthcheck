<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$days_to_expire = WP_Healthcheck::is_ssl_expiring();

if ( false === $days_to_expire || ! is_int( $days_to_expire ) ) {
	return false;
}

$status = ( $days_to_expire <= 0 ) ? 'expired' : 'expiring_soon';

$ssl_data = get_transient( WP_Healthcheck::SSL_DATA_TRANSIENT );

$issuer = ( empty( $ssl_data['issuer'] ) ) ? '' : ' (' . $ssl_data['issuer'] . ')';
$days   = _n( 'day', 'days', abs( $days_to_expire ), 'wp-healthcheck' );

$messages = array(
	'expiring_soon' => array(
		'class'   => 'notice-warning is-dismissible',
		/* translators: %1$s is the certificate issuer, %2$d is the number of days, %3$s is 'day' or 'days' string */
		'message' => sprintf( __( 'Your SSL certificate%1$s will expire in %2$d %3$s. Please don\'t forget to renew it!', 'wp-healthcheck' ), $issuer, $days_to_expire, $days ),
	),
	'expired'       => array(
		'class'   => 'notice-error is-dismissible',
		/* translators: %1$s is the certificate issuer, %2$d is the number of days, %3$s is 'day' or 'days' string */
		'message' => sprintf( __( 'Your SSL certificate%1$s has expired %2$d %3$s ago. Please renew it as soon as possible!', 'wp-healthcheck' ), $issuer, abs( $days_to_expire ), $days ),
	),
);
?>

<div class="notice wphc-notice wphc-notice-ssl <?php echo $messages[ $status ]['class']; ?>">
	<p>
		<strong>WP Healthcheck:</strong>
		<?php echo $messages[ $status ]['message']; ?>
	</p>
</div>
