<?php
namespace THSCD\WPHC\Core;

/**
 * The Loader class.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
final class Loader {
	/**
	 * The Autoload object.
	 *
	 * @since {VERSION}
	 *
	 * @var Autoload
	 */
	private $autoload;

	/**
	 * The SecureLogin object.
	 *
	 * @since {VERSION}
	 *
	 * @var SecureLogin
	 */
	private $secure_login;

	/**
	 * The Server object.
	 *
	 * @since {VERSION}
	 *
	 * @var Server
	 */
	private $server;

	/**
	 * The SSL object.
	 *
	 * @since {VERSION}
	 *
	 * @var SSL
	 */
	private $ssl;

	/**
	 * The Transients object.
	 *
	 * @since {VERSION}
	 *
	 * @var Transients
	 */
	private $transients;

	/**
	 * The WordPress object.
	 *
	 * @since {VERSION}
	 *
	 * @var WordPress
	 */
	private $wordpress;

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		$this->setup();
	}

	/**
	 * Get the Autoload object.
	 *
	 * @since {VERSION}
	 *
	 * @return Autoload
	 */
	public function autoload() {

		return $this->autoload;
	}

	/**
	 * Get the SecureLogin object.
	 *
	 * @since {VERSION}
	 *
	 * @return SecureLogin
	 */
	public function secure_login() {

		return $this->secure_login;
	}

	/**
	 * Get the Server object.
	 *
	 * @since {VERSION}
	 *
	 * @return Server
	 */
	public function server() {

		return $this->server;
	}

	/**
	 * Get the SSL object.
	 *
	 * @since {VERSION}
	 *
	 * @return SSL
	 */
	public function ssl() {

		return $this->ssl;
	}

	/**
	 * Get the Transients object.
	 *
	 * @since {VERSION}
	 *
	 * @return Transients
	 */
	public function transients() {

		return $this->transients;
	}

	/**
	 * Get the WordPress object.
	 *
	 * @since {VERSION}
	 *
	 * @return WordPress
	 */
	public function wordpress() {

		return $this->wordpress;
	}

	/**
	 * Set all the things up.
	 *
	 * @since {VERSION}
	 */
	public function setup() {

		// Loads the SecureLogin class globally.
		$this->secure_login = new SecureLogin();

		// Loads the plugin classes only if you are using Roles or WP-CLI.
		if ( is_admin() || wphc_is_doing_wpcli() ) {
			$this->server     = new Server();
			$this->ssl        = new SSL();
			$this->autoload   = new Autoload();
			$this->transients = new Transients();
			$this->wordpress  = new WordPress();
		}
	}
}
