<?php
namespace WPHC\Core;

/**
 * The CLI class.
 *
 * The WP Healthcheck extension for WP-CLI.
 *
 * @package wp-healthcheck
 *
 * @since 1.1.0
 */
class CLI extends \WP_CLI_Command {
	/**
	 * List the top WordPress autoload options.
	 *
	 * WordPress autoload options are very similar to transients. The main
	 * difference is: transients are used to store temporary data, while
	 * options are used to store permanent data.
	 *
	 * All the autoload options, as well as transients, are loaded
	 * automatically when WordPress loads itself. Thus, the number and size of
	 * these options can directly affect your site performance.
	 *
	 * When you deactivate an autoload option, you are not removing it. You are
	 * just telling WordPress to not load that option automatically on every
	 * request it does. In other words, the option will be loaded only when it
	 * is needed.
	 *
	 * ## OPTIONS
	 *
	 * [--deactivate=<option-name>]
	 * : Deactivate a specific option.
	 *
	 * [--history]
	 * : History of autoload options that were disabled through this plugin.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp healthcheck autoload
	 *     +-----------------------------+---------+
	 *     | name                        | size    |
	 *     +-----------------------------+---------+
	 *     | jetpack_available_modules   | 0.23 MB |
	 *     | wp_user_roles               | 0.07 MB |
	 *     | woocommerce_meta_box_errors | 0.04 MB |
	 *     | cron                        | 0.01 MB |
	 *     | siteurl                     | 0.00 MB |
	 *     | home                        | 0.00 MB |
	 *     | blogname                    | 0.00 MB |
	 *     | blogdescription             | 0.00 MB |
	 *     | users_can_register          | 0.00 MB |
	 *     | admin_email                 | 0.00 MB |
	 *     +-----------------------------+---------+
	 *
	 * @subcommand autoload
	 */
	public function autoload( $args, $assoc_args ) {
		if ( isset( $assoc_args['deactivate'] ) ) {
			$option_name = $assoc_args['deactivate'];

			if ( empty( $option_name ) || ! is_string( $option_name ) ) {
				\WP_CLI::error( 'You need to provide the name of the option to deactivate.' );
			}

			if ( ! get_option( $option_name ) ) {
				\WP_CLI::error( \WP_CLI::colorize( 'We couldn\'t find the %r' . $option_name . '%n option in your WordPress options table.' ) );
			}

			if ( wphc()->main->is_core_option( $option_name ) ) {
				\WP_CLI::error( 'You can\'t deactivate a WordPress core option.' );
			}

			if ( wphc()->main->is_autoload_disabled( $option_name ) ) {
				\WP_CLI::warning( \WP_CLI::colorize( 'The %y' . $option_name . '%n autoload option is already disabled.' ) );
				\WP_CLI::halt( 2 );
			}

			$deactivate = wphc()->main->deactivate_autoload_option( $option_name );

			if ( false !== $deactivate ) {
				\WP_CLI::success( \WP_CLI::colorize( 'Yay, the %y' . $option_name . '%n option was deactivated successfully.' ) );
			} else {
				\WP_CLI::error( \WP_CLI::colorize( 'Oops, for some reason we couldn\'t deactivate the %y' . $option_name . '%n option.' ) );
			}
		} elseif ( isset( $assoc_args['history'] ) ) {
			$opts = wphc()->main->get_autoload_history();

			if ( false === $opts || ! is_array( $opts ) || sizeof( $opts ) == 0 ) {
				\WP_CLI::warning( 'The history is empty.' );
				\WP_CLI::halt( 2 );
			}

			$list = array();

			foreach ( $opts as $name => $timestamp ) {
				$item = array(
					'name'              => $name,
					'deactivation_time' => date( 'Y-m-d H:i:s', $timestamp ),
				);

				$list[] = $item;
			}

			\WP_CLI\Utils\format_items( 'table', $list, array( 'name', 'deactivation_time' ) );
		} else {
			$autoload = wphc()->main->get_autoload_options();

			$this->_list_options( $autoload );
		}
	}

	/**
	 * List the server softwares and their respective versions.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp healthcheck server
	 *     +-------+--------------+--------+
	 *     | name  | version      | action |
	 *     +-------+--------------+--------+
	 *     | mysql | 5.7.20       | -      |
	 *     | php   | 7.0.22       | -      |
	 *     | wp    | 4.9          | -      |
	 *     | web   | nginx/1.13.7 | -      |
	 *     +-------+--------------+--------+
	 *
	 * @subcommand server
	 */
	public function server() {
		$info         = wphc()->main->get_server_data();
		$requirements = wphc()->main->get_server_requirements();

		$list = array();

		foreach ( $info as $name => $version ) {
			if ( empty( $version ) ) {
				continue;
			}

			if ( 'database' == $name ) {
				$name    = strtolower( $version['service'] );
				$version = $version['version'];
			}

			$status = wphc()->main->is_software_updated( $name );
			$action = '-';

			if ( 'wp' == $name && 'updated' != $status ) {
				$action = \WP_CLI::colorize( 'run %Ywp core update%n to update WordPress to latest version' );
			}

			if ( preg_match( '/(?:php|mysql|mariadb)/', $name ) ) {
				if ( 'outdated' == $status ) {
					$action = 'Your ' . strtoupper( $name ) . ' version is compatible with the current WordPress install. However, in order to get better performance and other improvements, you should consider to upgrade it to version ' . $requirements[ $name ]['recommended'] . ' or greater.';
				} elseif ( 'obsolete' == $status ) {
					$action = 'This ' . strtoupper( $name ) . ' version is not supported by WordPress anymore! Please upgrade it to version ' . $requirements[ $name ]['recommended'] . ' or greater.';
				}
			}

			if ( 'web' == $name && isset( $info['web'] ) && is_array( $info['web'] ) ) {
				if ( preg_match( '/(?:apache|nginx)/', $info['web']['service'] ) ) {
					$version = $info['web']['service'] . '/' . $info['web']['version'];
				} else {
					$version = $info['web']['version'];
				}
			}

			$item = array(
				'name'    => $name,
				'version' => $version,
				'action'  => $action,
			);

			if ( preg_match( '/(?:obsolete|outdated)/', $status ) ) {
				$color = ( 'obsolete' == $status ) ? 'r' : 'y';

				$item['version'] = \WP_CLI::colorize( '%' . $color . $version . '%n' );
			}

			$list[] = $item;
		}

		\WP_CLI\Utils\format_items( 'table', $list, array( 'name', 'version', 'action' ) );
	}

	/**
	 * If SSL is available, then list the certificate information.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp healthcheck ssl
	 *     +-------------+----------------------------+
	 *     | field       | value                      |
	 *     +-------------+----------------------------+
	 *     | common_name | tiagohillebrandt.eti.br    |
	 *     | issued_by   | Let's Encrypt Authority X3 |
	 *     | issued_on   | 2018-03-25 06:00:16        |
	 *     | expires_on  | 2018-06-23 06:00:16        |
	 *     +-------------+----------------------------+
	 *
	 * @subcommand ssl
	 */
	public function ssl( $args, $assoc_args ) {
		$ssl_data = wphc()->main->get_ssl_data();

		if ( false === $ssl_data || empty( $ssl_data ) ) {
			\WP_CLI::error( 'We couldn\'t find any SSL certificates associated with your site. Is HTTPS enabled?' );
		}

		$ssl_data = array(
			'common_name' => $ssl_data['common_name'],
			'issued_by'   => $ssl_data['issuer'],
			'issued_on'   => $ssl_data['validity']['from'],
			'expires_on'  => $ssl_data['validity']['to'],
		);

		$data = array();

		foreach ( $ssl_data as $key => $value ) {
			$data[] = array(
				'field' => $key,
				'value' => $value,
			);
		}

		\WP_CLI\Utils\format_items( 'table', $data, array( 'field', 'value' ) );
	}

	/**
	 * List the top WordPress transients.
	 *
	 * WordPress transients are used to temporarily cache specific data. For
	 * example, developers often use them to improve their themes and plugins
	 * performance by caching database queries and script results.
	 *
	 * However, some badly coded plugins and themes can store too much
	 * information on these transients, or can even create an excessively high
	 * number of transients, resulting in performance degradation.
	 *
	 * Cleaning up transients won't affect your site functionality. In fact,
	 * plugins, themes, and WordPress itself will recreate them according to
	 * their needs.
	 *
	 * ## OPTIONS
	 *
	 * [--delete-all]
	 * : Delete all the transients.
	 *
	 * [--delete-expired]
	 * : Delete the expired transients.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp healthcheck transient
	 *     +---------------------------------------------------------------+---------+
	 *     | name                                                          | size    |
	 *     +---------------------------------------------------------------+---------+
	 *     | feed_d117b5738fbd35bd8c0391cda1f2b5d9                         | 0.31 MB |
	 *     | feed_0d102f2a1f4d6bc90eb8c6ffe18e56ed                         | 0.10 MB |
	 *     | feed_ac0b00fe65abe10e0c5b588f3ed8c7ca                         | 0.09 MB |
	 *     | jetpack_file_data_5.5.1                                       | 0.03 MB |
	 *     | dash_v2_88ae138922fe95674369b1cb3d215a2b                      | 0.01 MB |
	 *     | files_154ef12729cd1d4938636e055ddc6924125ac651a58eaa9b11f0bcb | 0.01 MB |
	 *     | update_plugins                                                | 0.01 MB |
	 *     | wc_report_sales_by_date                                       | 0.00 MB |
	 *     | wphc_min_requirements                                         | 0.00 MB |
	 *     | update_core                                                   | 0.00 MB |
	 *     +---------------------------------------------------------------+---------+
	 *
	 * @subcommand transient
	 * @alias transients
	 */
	public function transient( $args, $assoc_args ) {
		if ( isset( $assoc_args['delete-all'] ) || isset( $assoc_args['delete-expired'] ) ) {
			$only_expired = ( isset( $assoc_args['delete-expired'] ) ) ? true : false;

			$message = ( wp_using_ext_object_cache() ) ? 'object cache items' : 'transients';

			if ( false !== wphc()->main->cleanup_transients( $only_expired ) ) {
				\WP_CLI::success( 'Yay! The ' . $message . ' were cleaned up successfully.' );
			} else {
				\WP_CLI::error( 'Oops, for some reason we couldn\'t clean up your ' . $message . '.' );
			}
		} else {
			if ( wp_using_ext_object_cache() ) {
				\WP_CLI::error( 'Unfortunately we cannot list the transients when an external object cache is being used.' );
			}

			$transients = wphc()->main->get_transients();

			$this->_list_options( $transients );
		}
	}

	/**
	 * List the name and size of the options in WP-CLI table format.
	 *
	 * @param array $data An array with name and size of the options.
	 */
	private function _list_options( $data ) {
		$list = array();

		foreach ( $data as $name => $size ) {
			$item = array(
				'name' => preg_replace( '/^(_site)?_transient_/', '', $name ),
				'size' => number_format( $size, 2 ) . ' MB',
			);

			$list[] = $item;
		}

		\WP_CLI\Utils\format_items( 'table', $list, array( 'name', 'size' ) );
	}
}

\WP_CLI::add_command( 'healthcheck', CLI::class );
