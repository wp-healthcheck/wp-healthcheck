<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$status = WP_Healthcheck::is_software_updated( 'php' );

if ( 'updated' == $status || false === $status ) {
    return false;
}

$requirements = WP_Healthcheck::get_server_requirements();
$server_data = WP_Healthcheck::get_server_data();

$messages = array(
    'outdated' => array(
        'class'   => 'notice-warning is-dismissible',
        /* translators: %1$s is the PHP version installed on current server, %2$s is the one WP team recommends */
        'message' => sprintf( __( 'Your PHP version (%1$s) is compatible with the current WordPress install. However, in order to get better performance and other improvements, the <a href="https://wordpress.org/about/requirements/">WordPress team recommends</a> you upgrade your server to PHP version %2$s or greater.', 'wp-healthcheck' ), $server_data['php'], $requirements['php']['recommended'] ),
    ),
    'obsolete' => array(
        'class'   => 'notice-error is-dismissible',
        /* translators: %1$s is the PHP version installed on current server, %2$s is the one WP team recommends */
        'message' => sprintf( __( 'The PHP version you are using (%1$s) is not supported by WordPress anymore! Please contact your developers and/or hosting company to upgrade your PHP to version %2$s or greater.', 'wp-healthcheck' ), $server_data['php'], $requirements['php']['recommended'] ),
    ),
);
?>
<div class="notice wphc-notice wphc-notice-php <?php echo $messages[ $status ]['class']; ?>">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php echo $messages[ $status ]['message']; ?>
  </p>
</div>
