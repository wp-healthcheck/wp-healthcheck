<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$requirements = WP_Healthcheck::get_server_requirements();
$server_data = WP_Healthcheck::get_server_data();

if ( empty( $server_data['web'] && ! preg_match( '/nginx|apache/i', $server_data['web']['service'] ) ) ) {
    return false;
}

$status = WP_Healthcheck::is_software_updated( $server_data['web']['service'] );

if ( 'updated' == $status || false === $status ) {
    return false;
}

$messages = array(
    'outdated' => array(
        'class'   => 'notice-warning is-dismissible',
        /* translators: %1$s is the current database service (MySQL, MariaDB, etc), %2$s is the database version installed on server, %3$s is the version that WP team recommends */
        'message' => sprintf( __( 'Your %1$s version (%2$s) is not recommended with. Please, update your web server to %3$s or greater.', 'wp-healthcheck' ), $server_data['web']['service'], $server_data['web']['version'], $requirements[ $server_data['web']['service'] ]['recommended'] ),
    ),
    'obsolete' => array(
        'class'   => 'notice-error is-dismissible',
        /* translators: %1$s is the current database service (MySQL, MariaDB, etc), %2$s is the database version installed on server, %3$s is the version that WP team recommends */
        'message' => sprintf( __( 'The %1$s version you are using (%2$s) is not recommended and is deprecated! Please contact your developers and/or hosting company to upgrade your %1$s to version %3$s or greater.', 'wp-healthcheck' ), ucfirst( $server_data['web']['service'] ), $server_data['web']['version'], $requirements[ $server_data['web']['service'] ]['recommended'] ),
    ),
);
?>
<div class="notice wphc-notice wphc-notice-web <?php echo $messages[ $status ]['class']; ?>">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php echo $messages[ $status ]['message']; ?>
  </p>
</div>
