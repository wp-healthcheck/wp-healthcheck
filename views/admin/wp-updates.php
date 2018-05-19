<?php
if ( ! defined( 'WPHC' ) ) {
    exit;
}
?>

<p><?php echo esc_html_e( 'The default WordPress behavior is to always update itself, automatically, to the latest minor release available. For example, if you are running WordPress 4.9.5, it will be automatically updated to 4.9.6 once it is released.', 'wp-healthcheck' ); ?></p>

<p><strong>Minor</strong> updates are released more often than major ones. These releases usually includes security updates, fixes, and enhancements. <strong>Major</strong> updates are released 3-4 times a year, and they always include new features, major enhancements, and bug fixes to WordPress.</p>

<p>
    WordPress Update Policy:
    <select>
        <option value="minor">Install minor updates automatically</option>
        <option value="major">Install major and minor updates automatically</option>
        <option value="disabled">Disable updates on my site (not recommended)</option>
    </select>

    <button type="button" class="button" id="wphc-btn-core-updates">Apply</button>
</p>

<!--
Currently your WordPress Automatic Background Updates are being managed in your wp-config.php file. Please remove the WP_AUTO_UPDATE_CORE or AUTOMATIC_UPDATER_DISABLED constant to use this feature.
//-->
