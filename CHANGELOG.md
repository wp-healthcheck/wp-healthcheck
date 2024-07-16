# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.0] - 2024-07-16
### Changed
- Bumped the minimum PHP version up to 7.0.
- Bumped the minimum WordPress version up to 5.0.
- Bumped the tested WP version up to 6.5.5.

### Fixed
- Added sanitization to some specific inputs.

## [1.3.4] - 2022-03-29
### Changed
- Updated the user agent for the API request.
- Bumped the tested WP version up to 5.9.2.

## [1.3.3] - 2021-02-15
### Changed
- Bumped up the tested WP version.

## [1.3.2] - 2019-01-25
### Changed
- Bumped the tested WP version up to 5.0.3.

## [1.3.1] - 2018-06-03
### Fixed
- Fixed an issue when trying to retrieve web server details on recent PHP versions ([#35](https://github.com/wp-healthcheck/wp-healthcheck/issues/35)).
- Fixed a couple of UI issues ([#32](https://github.com/wp-healthcheck/wp-healthcheck/issues/32)).

## [1.3.0] - 2018-06-01
### Added
- Recommend to install Let's Encrypt certificate if HTTPS is not enabled ([#24](https://github.com/wp-healthcheck/wp-healthcheck/issues/24)).
- Ability to define the WordPress auto update policy (thanks to [@marksabbath](https://github.com/marksabbath/) for back end implementation [[#19](https://github.com/wp-healthcheck/wp-healthcheck/issues/19)]).
- Check for obsolete plugins using the WordPress Plugins API ([#21](https://github.com/wp-healthcheck/wp-healthcheck/issues/21)).
- WP-CLI support to verify SSL certificate details (issuer, expiration, etc) ([#18](https://github.com/wp-healthcheck/wp-healthcheck/issues/18)).

## [1.2.1] - 2018-02-17
### Fixed
- Fixed warnings when the server software is not found ([#15](https://github.com/wp-healthcheck/wp-healthcheck/issues/15)).

## [1.2] - 2018-01-20
### Added
- Display an admin notice when your SSL certificate is about to expire or already expired.

### Fixed
- In some cases, MariaDB version from db_version() was incorrect.
- Hide the web server admin notice when the version was not retrieved properly.

## [1.1] - 2017-12-08
### Added
- Ability to reactivate autoload options disabled through the plugin.
- WP-CLI extension.
- Check the web server (NGINX/Apache) versions (thanks to [@marksabbath](https://github.com/marksabbath/)).
- Check the MariaDB version (thanks to [@marksabbath](https://github.com/marksabbath/)).
- Check for WordPress trunk updates.
- Hide 'Clear Expired Transients' button for WordPress 4.9+ users.

## [1.0] - 2017-11-17
### Added
- Initial release.
