=== WP Healthcheck ===
Contributors: tiagohillebrandt, marksabbath
Tags: performance, transients, autoload, cron, healthcheck, load time, ssl, https, check
Requires at least: 3.9
Tested up to: 4.9.6
Requires PHP: 5.5
Stable tag: 1.3.1
License: GPLv3+
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WP Healthcheck is a plugin to check the health of your WordPress install.

== Description ==

[WP Healthcheck](https://wp-healthcheck.com) is a plugin to check the health of your WordPress install.

It detects some useful information regarding your site health, like the number of active transients and autoload options, and then displays them conveniently via the WordPress Dashboard.

This plugin can help you improve your site performance by cleaning up the transients and deactivating autoload options.

WP Healthcheck also verifies the software versions in use by your server. We maintain information about software minimum requirements up to date in our systems. This allows the plugin to retrieve this information from our external API and compare versus the ones installed in your server.

SSL certificate expiration dates are also checked and notifications for expired or near expiration SSL certificates are provided conveniently via the WordPress Dashboard.

= WP-CLI Extension =

WP Healthcheck also includes a WP-CLI extension. If you want to see all the CLI commands available in the plugin, go ahead and run `wp healthcheck` in your site.

* <code>wp healthcheck autoload [--deactivate=<option-name>] [--history]</code>
* <code>wp healthcheck transient [--delete-expired] [--delete-all]</code>
* <code>wp healthcheck server</code>
* <code>wp healthcheck ssl</code>

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

* Ability to check the health of your WP Cron.
* Check for secure headers (HTTPS).
* Better support to external object caching.
* Security check.

If you have any other features that you would like to see available in WP Healthcheck, please send them over to `tiago AT tiagohillebrandt DOT com`.

= How can I contribute to the project? =

If you are a developer and want to contribute writing new features, enhancements, bug fixes, or tests, please send your Pull Requests to our [GitHub repository](https://github.com/wp-healthcheck/wp-healthcheck).

== Screenshots ==

1. WP Healthcheck admin.
2. Admin notices.
3. WP-CLI extension.

== Changelog ==

= [1.3.4] 2022-03-29 =
* Change: Update the user agent for the API request.
* Bump up the WP supported version.

= [1.3.3] 2021-02-15 =
* Bump up the WP supported version.

= [1.3.2] 2019-01-25 =
* Update readme.txt to update WordPress version compatibility to 5.0.3.

= [1.3.1] 2018-06-03 =
* Hotfix: Fixes an issue when trying to retrieve web server details on recent PHP versions ([#35](https://github.com/wp-healthcheck/wp-healthcheck/issues/35)).
* Hotfix: Fixes couple of UI issues ([#32](https://github.com/wp-healthcheck/wp-healthcheck/issues/32)).

= [1.3.0] 2018-06-01 =
* Feature: Recommend to install Let's Encrypt certificate if HTTPS is not enabled ([#24](https://github.com/wp-healthcheck/wp-healthcheck/issues/24)).
* Feature: Ability to define the WordPress auto update policy (thanks to [@marksabbath](https://github.com/marksabbath/) for back end implementation [[#19](https://github.com/wp-healthcheck/wp-healthcheck/issues/19)]).
* Feature: Check for obsolete plugins using the WordPress Plugins API ([#21](https://github.com/wp-healthcheck/wp-healthcheck/issues/21)).
* Feature: WP-CLI support to verify SSL certificate details (issuer, expiration, etc) ([#18](https://github.com/wp-healthcheck/wp-healthcheck/issues/18)).

= [1.2.1] 2018-02-17 =
* Hotfix: Fixes warnings when server software is not found ([#15](https://github.com/wp-healthcheck/wp-healthcheck/issues/15)).

= [1.2] 2018-01-20 =
* Feature: Display an admin notice when your SSL certificate is about to expire or already expired.
* Hotfix: In some cases, MariaDB version from db_version() was incorrect.
* Hotfix: Hide the web server admin notice when the version was not retrieved properly.

= [1.1] 2017-12-08 =
* Feature: Ability to reactivate autoload options disabled through the plugin.
* Feature: WP-CLI extension.
* Feature: Check the web server (NGINX/Apache) versions (thanks to [@marksabbath](https://github.com/marksabbath/)).
* Feature: Check the MariaDB version (thanks to [@marksabbath](https://github.com/marksabbath/)).
* Feature: Check for WordPress trunk updates.
* Feature: Hide 'Clear Expired Transients' button for WordPress 4.9+ users.

= [1.0] 2017-11-17 =
* Initial release.
