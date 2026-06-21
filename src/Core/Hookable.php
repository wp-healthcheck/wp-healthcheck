<?php
/**
 * Hookable contract.
 *
 * Implemented by services that need to register WordPress hooks. The
 * registration is performed by the Bootstrap at a deterministic point in the
 * request lifecycle, decoupling hook registration from object construction.
 *
 * @package wp-healthcheck
 * @since {VERSION}
 */

namespace THSCD\WPHC\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface Hookable.
 *
 * A service implementing this interface declares that it wires itself into
 * WordPress through actions and filters. Bootstrap calls hooks() once, in a
 * known order, so the timing of hook registration never depends on when the
 * service happens to be resolved from the container.
 *
 * @since {VERSION}
 */
interface Hookable {

	/**
	 * Register the WordPress hooks for this service.
	 *
	 * Bootstrap guarantees a single call per request, but implementations
	 * should not assume any particular WordPress hook has already fired.
	 *
	 * @since {VERSION}
	 */
	public function hooks();
}
