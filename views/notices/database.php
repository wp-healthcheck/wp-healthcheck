<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$requirements = WP_Healthcheck::get_server_requirements();
$server_data = WP_Healthcheck::get_server_data();

$db_service = $server_data['database']['service'];

$status = WP_Healthcheck::is_software_updated( strtolower( $db_service ) );

if ( 'updated' == $status || false === $status ) {
    return false;
}

$messages = array(
    'outdated' => array(
        'class'   => 'notice-warning is-dismissible',
        /* translators: %1$s is the current database service (MySQL, MariaDB, etc), %2$s is the database version installed on server, %3$s is the version that WP team recommends */
        'message' => sprintf( __( 'Your %1$s version (%2$s) is compatible with the current WordPress install. However, the <a href="https://wordpress.org/about/requirements/">WordPress team recommends</a> you upgrade your server to %3$s or greater.', 'wp-healthcheck' ), $db_service, $server_data['database']['version'], $requirements[ strtolower( $db_service ) ]['recommended'] ),
    ),
    'obsolete' => array(
        'class'   => 'notice-error is-dismissible',
        /* translators: %1$s is the current database service (MySQL, MariaDB, etc), %2$s is the database version installed on server, %3$s is the version that WP team recommends */
        'message' => sprintf( __( 'The %1$s version you are using (%2$s) is not supported by WordPress anymore! Please contact your developers and/or hosting company to upgrade your %1$s to version %3$s or greater.', 'wp-healthcheck' ), $db_service, $server_data['database']['version'], $requirements[ strtolower( $db_service ) ]['recommended'] ),
    ),
);
?>
<div class="notice wphc-notice wphc-notice-database <?php echo $messages[ $status ]['class']; ?>">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php echo $messages[ $status ]['message']; ?>
  </p>
</div>
