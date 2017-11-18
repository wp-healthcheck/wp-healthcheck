=== WP Healthcheck ===
Contributors: tiagohillebrandt
Tags: performance, transients, autoload, cron, healthcheck, load time
Requires at least: 3.9
Tested up to: 4.9
Requires PHP: 5.5
Stable tag: 1.0
License: GPLv3+
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WP Healthcheck is a plugin to check the health of your WordPress install.

== Description ==

[WP Healthcheck](https://wp-healthcheck.com) is a plugin to check the health of your WordPress install.

It collects some useful information regarding your site health, like the number of active transients and autoload options, and then bring them up to you via Dashboard.

And after that, it can help you to improve your site performance by cleaning up the transients and deactivating autoload options.

Also, the WP Healthcheck verifies for the version of the softwares installed in your server. To execute this verification, the plugin retrieve data from our [external API](https://api.wp-healthcheck.com/v1/requirements) and then compare the versions available in our API versus the ones installed in your server.

== Frequently Asked Questions ==

= Where can I get support and talk to other users? =

If you have any questions, you can post a new thread in our [Community Forum](https://wordpress.org/support/plugin/wp-healthcheck), available on WordPress.org.

We review it weekly and our team will be happy to assist you there.

For Premium Support, you can contact us through our [website](https://wp-healthcheck.com).

= Where can I report any bugs? =

Please report any bugs to our [issues](https://github.com/wp-healthcheck/wp-healthcheck/issues) page.

If you are not sure if something is a bug or not, you can always ask for guidance in our [Community Forum](https://wordpress.org/support/plugin/wp-healthcheck).

= How can I translate WP Healthcheck to my language? =

You can translate it to your language through the [WordPress translations platform](https://translate.wordpress.org/projects/wp-plugins/wp-healthcheck/stable).

Alternatively, you can find the POT file available at `/languages/wp-healthcheck.pot`. Go ahead and start a PO file for your language from that template (POT file).

= Where can I request new features? =

We already have some features planned for coming versions:

* WP-CLI extension.
* Ability to check the health of your WP Cron.
* Ability to enable back an autoload option that was disabled through the plugin.
* Check for secure headers (HTTPS).
* MariaDB version checker support.

If you have any other features that you would like to see available in WP Healthcheck, please send them over to `tiago AT tiagohillebrandt DOT com`.

= How can I contribute to the project? =

If you are a developer and want to contribute writing new features, enhancements, bug fixes, or tests, please send your Pull Requests to our [GitHub repository](https://github.com/wp-healthcheck/wp-healthcheck).

== Screenshots ==

1. WP Healthcheck admin.
2. Admin notices.

== Changelog ==

= [1.0] 2017-11-17 =
* Initial release.
