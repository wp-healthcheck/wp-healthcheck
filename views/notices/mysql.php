<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$status = WP_Healthcheck::is_software_updated( 'mysql' );

if ( 'updated' == $status || false === $status ) {
    return false;
}

$requirements = WP_Healthcheck::get_server_requirements();
$server_data = WP_Healthcheck::get_server_data();

$messages = array(
    'outdated' => array(
        'class'   => 'notice-warning is-dismissible',
        /* translators: %1$s is the MySQL version installed on server, %2$s is the one WP team recommends */
        'message' => sprintf( __( 'Your MySQL version (%1$s) is compatible with the current WordPress install. However, the <a href="https://wordpress.org/about/requirements/">WordPress team recommends</a> you upgrade your server to MySQL %2$s or greater.', 'wp-healthcheck' ), $server_data['mysql'], $requirements['mysql']['recommended'] ),
    ),
    'obsolete' => array(
        'class'   => 'notice-error is-dismissible',
        /* translators: %1$s is the MySQL version installed on server, %2$s is the one WP team recommends */
        'message' => sprintf( __( 'The MySQL version you are using (%1$s) is not supported by WordPress anymore! Please contact your developers and/or hosting company to upgrade your MySQL to version %2$s or greater.', 'wp-healthcheck' ), $server_data['mysql'], $requirements['mysql']['recommended'] ),
    ),
);
?>
<div class="notice wphc-notice wphc-notice-mysql <?php echo $messages[ $status ]['class']; ?>">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php echo $messages[ $status ]['message']; ?>
  </p>
</div>
