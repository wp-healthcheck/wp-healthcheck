<?php
if ( ! defined( 'WPHC' ) ) {
	exit;
}

$days_to_expire = wphc( 'module.ssl' )->is_expiring();

if ( $days_to_expire === false || ! is_int( $days_to_expire ) ) {
	return false;
}

$cert_status = ( $days_to_expire <= 0 ) ? 'expired' : 'expiring_soon';

$ssl_data = wphc( 'module.ssl' )->get_data();

$issuer = ( empty( $ssl_data['issuer'] ) ) ? '' : ' (' . $ssl_data['issuer'] . ')';
$days   = _n( 'day', 'days', abs( $days_to_expire ), 'wp-healthcheck' );

$messages = [
	'expiring_soon' => [
		'class'   => 'notice-warning is-dismissible',
		/* translators: %1$s is the certificate issuer, %2$d is the number of days, %3$s is 'day' or 'days' string. */
		'message' => sprintf( __( 'Your SSL certificate%1$s will expire in %2$d %3$s. Please don\'t forget to renew it!', 'wp-healthcheck' ), $issuer, $days_to_expire, $days ),
	],
	'expired'       => [
		'class'   => 'notice-error is-dismissible',
		/* translators: %1$s is the certificate issuer, %2$d is the number of days, %3$s is 'day' or 'days' string. */
		'message' => sprintf( __( 'Your SSL certificate%1$s has expired %2$d %3$s ago. Please renew it as soon as possible!', 'wp-healthcheck' ), $issuer, abs( $days_to_expire ), $days ),
	],
];
?>

<div class="notice wphc-notice wphc-notice-ssl <?php echo esc_attr( $messages[ $cert_status ]['class'] ); ?>">
	<p>
		<strong>WP Healthcheck:</strong>
		<?php echo esc_html( $messages[ $cert_status ]['message'] ); ?>
	</p>
</div>
