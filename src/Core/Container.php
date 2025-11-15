<?php

namespace THSCD\WPHC\Core;

/**
 * Service Container
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */
use THSCD\WPHC\Modules\Autoload;
use THSCD\WPHC\Modules\Plugins;
use THSCD\WPHC\Modules\Server;
use THSCD\WPHC\Modules\SSL;
use THSCD\WPHC\Modules\Transients;
use THSCD\WPHC\Modules\WordPress;
use THSCD\WPHC\Admin\Dashboard;
use THSCD\WPHC\Admin\AJAX;
use THSCD\WPHC\Admin\Metaboxes;
use THSCD\WPHC\Admin\Pointers;
use THSCD\WPHC\Admin\Notices;
use THSCD\WPHC\Utils\Install;
use THSCD\WPHC\Utils\Upgrade;
use THSCD\WPHC\Utils\View;
use Exception;

/**
 * Class Container
 *
 * PSR-11 compatible dependency injection container.
 * Implements get() and has() methods as per PSR-11 standard.
 */
class Container {

	/**
	 * The current globally available container instance.
	 *
	 * @since {VERSION}
	 *
	 * @var Container|null
	 */
	protected static $instance;

	/**
	 * The container's registered services.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	protected $services = [];

	/**
	 * The container's singleton instances.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * Core services mapping.
	 *
	 * Maps service names to their class names.
	 *
	 * @since {VERSION}
	 *
	 * @var array
	 */
	protected $bindings = [
		// Modules.
		'module.autoload'   => Autoload::class,
		'module.plugins'    => Plugins::class,
		'module.server'     => Server::class,
		'module.ssl'        => SSL::class,
		'module.transients' => Transients::class,
		'module.wordpress'  => WordPress::class,

		// Admin.
		'admin.dashboard'  => Dashboard::class,
		'admin.ajax'       => AJAX::class,
		'admin.metaboxes'  => Metaboxes::class,
		'admin.pointers'   => Pointers::class,
		'admin.notices'    => Notices::class,

		// Utils.
		'util.install' => Install::class,
		'util.upgrade' => Upgrade::class,
		'util.view'    => View::class,
	];

	/**
	 * Get the globally available instance of the container.
	 *
	 * @since {VERSION}
	 *
	 * @return Container
	 */
	public static function get_instance() {

		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Bind a service to the container.
	 *
	 * @since {VERSION}
	 *
	 * @param string          $name     Service name.
	 * @param string|callable $concrete Class name or factory callback.
	 * @param bool            $shared   Whether the service is a singleton.
	 */
	public function bind( $name, $concrete, $shared = false ) {

		$this->services[ $name ] = [
			'concrete' => $concrete,
			'shared'   => $shared,
		];
	}

	/**
	 * Register a singleton service in the container.
	 *
	 * @since {VERSION}
	 *
	 * @param string          $name     Service name.
	 * @param string|callable $concrete Class name or factory callback.
	 */
	public function singleton( $name, $concrete ) {

		$this->bind( $name, $concrete, true );
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * PSR-11 method signature.
	 *
	 * @since {VERSION}
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return mixed Entry.
	 * @throws Exception No entry was found for this identifier.
	 */
	public function get( $id ) {

		// Return existing singleton instance.
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		// Get the concrete implementation.
		$concrete = $this->get_concrete( $id );

		if ( is_null( $concrete ) ) {
			throw new Exception( "Service [{$id}] not found in container." );
		}

		try {
			// Build the instance.
			$instance = $this->build( $concrete );

			// Store singleton instances.
			if ( $this->is_shared( $id ) ) {
				$this->instances[ $id ] = $instance;
			}

			return $instance;
		} catch ( Exception $e ) {
			throw new Exception( "Error resolving service [{$id}]: " . $e->getMessage(), 0, $e );
		}
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * PSR-11 method signature.
	 *
	 * @since {VERSION}
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return bool
	 */
	public function has( $id ) {

		return isset( $this->services[ $id ] ) ||
			   isset( $this->instances[ $id ] ) ||
			   isset( $this->bindings[ $id ] );
	}

	/**
	 * Get the concrete type for a service.
	 *
	 * @since {VERSION}
	 *
	 * @param string $name Service name.
	 *
	 * @return string|callable|null
	 */
	protected function get_concrete( $name ) {

		// Check custom services first.
		if ( isset( $this->services[ $name ] ) ) {
			return $this->services[ $name ]['concrete'];
		}

		// Fall back to core bindings.
		if ( isset( $this->bindings[ $name ] ) ) {
			return $this->bindings[ $name ];
		}

		return null;
	}

	/**
	 * Build an instance of the given concrete type.
	 *
	 * @since {VERSION}
	 *
	 * @param string|callable $concrete Class name or factory callback.
	 *
	 * @return mixed
	 */
	protected function build( $concrete ) {

		// If it's a callback, execute it.
		if ( is_callable( $concrete ) ) {
			return $concrete( $this );
		}

		// Otherwise, instantiate the class.
		return new $concrete();
	}

	/**
	 * Determine if a service is shared (singleton).
	 *
	 * @since {VERSION}
	 *
	 * @param string $name Service name.
	 *
	 * @return bool
	 */
	protected function is_shared( $name ) {

		// Custom services have explicit sharing flag.
		if ( isset( $this->services[ $name ] ) ) {
			return $this->services[ $name ]['shared'];
		}

		// Core bindings are always singletons.
		return isset( $this->bindings[ $name ] );
	}

	/**
	 * Clear all services and instances.
	 *
	 * Useful for testing.
	 *
	 * @since {VERSION}
	 */
	public function flush() {

		$this->services  = [];
		$this->instances = [];
	}
}
