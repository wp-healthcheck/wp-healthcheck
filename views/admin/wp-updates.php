<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}
?>

<p><?php esc_html_e( 'The default WordPress behavior is to always update automatically to the latest minor release available. For example, WordPress 4.9.5 will automatically be updated to 4.9.6 upon release.', 'wp-healthcheck' ); ?></p>

<p><?php _e( '<strong>Minor</strong> updates are released more often than major ones. These releases usually includes security updates, fixes, and enhancements. <strong>Major</strong> updates are released 3-4 times a year, and they always include new features, major enhancements, and bug fixes to WordPress.', 'wp-healthcheck' ); ?></p>

<?php if ( WP_Healthcheck::get_core_auto_update_option() !== false ) : ?>
    <?php
    $wp_update_options = array(
        'minor'    => esc_html__( 'Install minor updates automatically.', 'wp-healthcheck' ),
        'major'    => esc_html__( 'Install major and minor updates automatically.', 'wp-healthcheck' ),
        'disabled' => esc_html__( 'Disable automatic updates on my site (not recommended).', 'wp-healthcheck' ),
    );
    ?>

    <p><?php esc_html_e( 'WordPress Update Policy:', 'wp-healthcheck' ); ?></p>

    <p class="wphc_wp_auto_update">
        <?php foreach ( $wp_update_options as $option => $description ) : ?>
            <span><label><input id="wphc-opt-wp-updates" name="wphc-opt-wp-updates" type="radio" value="<?php echo $option; ?>" <?php echo ( WP_Healthcheck::get_core_auto_update_option() === $option ) ? 'checked="checked"' : ''; ?> /> <?php echo $description; ?></label></span>
        <?php endforeach; ?>
    </p>

    <p><button type="button" class="button" id="wphc-btn-wp-auto-update"><?php esc_html_e( 'Apply Update Policy', 'wp-healthcheck' ); ?></button></p>

    <p class="wphc_wp_auto_update_success"><?php esc_html_e( 'Done! The new WordPress Update Policy has been applied successfully.', 'wp-healthcheck' ); ?></p>
    <p class="wphc_wp_auto_update_fail"><?php esc_html_e( 'Something went wrong. Please try again!', 'wp-healthcheck' ); ?></p>
<?php else : ?>
    <p class="wphc_wp_auto_update_error"><?php esc_html_e( 'Currently your WordPress Automatic Background Updates are being managed in your wp-config.php file. Please remove the WP_AUTO_UPDATE_CORE or AUTOMATIC_UPDATER_DISABLED constants to use this feature.', 'wp-healthcheck' ); ?></p>
<?php endif; ?>
