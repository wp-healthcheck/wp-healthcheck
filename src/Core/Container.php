<?php
/**
 * Service Container
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */

namespace THSCD\WPHC\Core;

use Exception;

/**
 * Class Container.
 *
 * Lightweight service container implementing the Service Locator pattern.
 * Services are registered by the Bootstrap (the composition root) and resolved
 * on demand; the container itself is intentionally agnostic about the
 * application's concrete classes.
 *
 * @since {VERSION}
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

		if ( ! isset( $this->services[ $id ] ) ) {
			throw new Exception( esc_html( "Service [{$id}] not found in container." ) );
		}

		// Build the instance.
		$instance = $this->build( $this->services[ $id ]['concrete'] );

		// Store singleton instances.
		if ( $this->services[ $id ]['shared'] ) {
			$this->instances[ $id ] = $instance;
		}

		return $instance;
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
}
