<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}

$requirements = WP_Healthcheck::get_server_requirements();
$server_data = WP_Healthcheck::get_server_data();

$status = WP_Healthcheck::is_software_updated( strtolower( $server_data['database']['brand'] ) );

if ( 'updated' == $status || false === $status ) {
    return false;
}

$db_brand = $server_data['database']['brand'];

$messages = array(
    'outdated' => array(
        'class'   => 'notice-warning is-dismissible',
        /* translators: %1$s is the database in use, %2$s is the Database version installed on server, %3$s is the one WP team recommends */
        'message' => sprintf( __( 'Your %1$s version (%2$s) is compatible with the current WordPress install. However, the <a href="https://wordpress.org/about/requirements/">WordPress team recommends</a> you upgrade your server to %2$s or greater.', 'wp-healthcheck' ), $db_brand, $server_data['database']['version'], $requirements[ strtolower( $db_brand ) ]['recommended'] ),
    ),
    'obsolete' => array(
        'class'   => 'notice-error is-dismissible',
        /* translators: %1$s is the database in use, %2$s is the Database version installed on server, %3$s is the one WP team recommends */
        'message' => sprintf( __( 'The %1$s version you are using (%2$s) is not supported by WordPress anymore! Please contact your developers and/or hosting company to upgrade your %1$s to version %3$s or greater.', 'wp-healthcheck' ), $db_brand, $server_data['database']['version'], $requirements[ strtolower( $db_brand ) ]['recommended'] ),
    ),
);
?>
<div class="notice wphc-notice wphc-notice-database <?php echo $messages[ $status ]['class']; ?>">
  <p>
    <strong>WP Healthcheck:</strong>
    <?php echo $messages[ $status ]['message']; ?>
  </p>
</div>
