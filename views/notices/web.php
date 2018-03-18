<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$requirements = WP_Healthcheck::get_server_requirements();
$server_data = WP_Healthcheck::get_server_data();

if ( empty( $server_data['web']['service'] ) || empty( $server_data['web']['version'] ) || ! preg_match( '/(nginx|apache)/i', $server_data['web']['service'] ) ) {
    return false;
}

$service = $server_data['web']['service'];

$status = WP_Healthcheck::is_software_updated( $service );

if ( 'updated' == $status || false === $status ) {
    return false;
}

$messages = array(
    'outdated' => array(
        'class'   => 'notice-warning is-dismissible',
        /* translators: %1$s is the current web server (NGINX, Apache, etc), %2$s is the version of the web server installed on server, %3$s is the version that we recommend to use */
        'message' => sprintf( __( 'Your %1$s version (%2$s) is not officially supported anymore! In order to get better performance and improvements, we recommend you upgrade your server to %1$s %3$s or greater.', 'wp-healthcheck' ), ucfirst( $service ), $server_data['web']['version'], $requirements[ $service ]['recommended'] ),
    ),
    'obsolete' => array(
        'class'   => 'notice-error is-dismissible',
        /* translators: %1$s is the current web server (NGINX, Apache, etc), %2$s is the version of the web server installed on server, %3$s is the version that we recommend to use */
        'message' => sprintf( __( 'Your %1$s version (%2$s) is obsolete and is not officially supported anymore! Please contact your developers and/or hosting company to upgrade your %1$s to version %3$s or greater as soon as possible.', 'wp-healthcheck' ), ucfirst( $service ), $server_data['web']['version'], $requirements[ $service ]['recommended'] ),
    ),
);
?>
<div class="notice wphc-notice wphc-notice-web <?php echo $messages[ $status ]['class']; ?>">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php echo $messages[ $status ]['message']; ?>
  </p>
</div>
