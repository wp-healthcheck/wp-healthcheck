<?php
namespace THSCD\WPHC;

use THSCD\WPHC\Utils\Install;
use THSCD\WPHC\Utils\Upgrade;

/**
 * The Loader class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
final class Loader {

	/**
	 * The Core\Loader object.
	 *
	 * @since {VERSION}
	 *
	 * @var Core\Loader
	 */
	private $core;

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * WordPress actions and filters.
	 *
	 * @since {VERSION}
	 */
	public function hooks() {

		add_action( 'plugins_loaded', [ $this, 'setup' ] );
	}

	/**
	 * Load the plugin classes.
	 *
	 * @since {VERSION}
	 */
	public function setup() {

		// Loads the helper functions.
		require_once WPHC_PLUGIN_DIR . '/inc/helpers.php';

		// Loads the Upgrade class.
		new Upgrade();

		// Loads the plugin classes.
		$this->core  = new Core\Loader();

		// Loads the plugin hooks.
		new Install();
	}

	/**
	 * Get the Core\Loader object.
	 *
	 * @since {VERSION}
	 *
	 * @return Core\Loader
	 */
	public function core() {

		return $this->core;
	}
}
