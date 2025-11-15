<?php
class AutoloadTest extends WP_UnitTestCase {
	public function test_autoload_history() {
		$options = wphc( 'autoload' )->get_history();

		$this->assertFalse( $options );

		$name = 'wphc_autoload_option';

		add_option( $name, 'autoload' );

		wphc( 'autoload' )->deactivate( $name );

		$options = wphc( 'autoload' )->get_history();

		$this->assertInternalType( 'array', $options );
		$this->assertGreaterThan( 0, sizeof( $options ) );

		delete_option( $name );

		wp_cache_flush();
	}

	public function test_autoload_options() {
		$options = wphc( 'autoload' )->get();

		$this->assertInternalType( 'array', $options );
		$this->assertGreaterThan( 0, sizeof( $options ) );

		foreach ( $options as $name => $size ) {
			$this->assertInternalType( 'float', $size );
		}
	}

	public function test_autoload_stats() {
		$stats = wphc( 'autoload' )->get_stats();

		$this->assertInternalType( 'array', $stats );
		$this->assertGreaterThan( 0, sizeof( $stats ) );

		$keys = [
			'count' => 'int',
			'size'  => 'float',
		];

		foreach ( $keys as $key => $type ) {
			$this->assertArrayHasKey( $key, $stats );
			$this->assertInternalType( $type, $stats[ $key ] );
		}
	}

	public function test_cleanup_plugin_options() {
		add_option( WP_Healthcheck::DISABLE_AUTOLOAD_OPTION, 'test' );

		WP_Healthcheck::_cleanup_options( false );

		$this->assertFalse( get_option( WP_Healthcheck::DISABLE_AUTOLOAD_OPTION ) );
	}

	public function test_deactivate_autoload_option() {
		$name = 'wphc_autoload_option';

		add_option( $name, 'autoload' );

		$this->assertInternalType( 'int', wphc( 'autoload' )->deactivate( $name ) );

		$history = wphc( 'autoload' )->get_history();

		$this->assertNotFalse( $history );
		$this->assertInternalType( 'array', $history );
		$this->assertArrayHasKey( $name, $history );

		delete_option( $name );

		wp_cache_flush();

		add_option( $name, 'autoload' );

		$this->assertInternalType( 'int', wphc( 'autoload' )->deactivate( $name, false ) );

		$history = wphc( 'autoload' )->get_history();

		$this->assertFalse( $history );

		$this->assertFalse( wphc( 'autoload' )->deactivate( $name ) );

		delete_option( $name );

		wp_cache_flush();
	}

	public function test_is_autoload_disabled() {
		$status = wphc( 'autoload' )->is_deactivated( 'siteurl' );

		$this->assertFalse( $status );

		$name = 'wphc_autoload_option';

		add_option( $name, 'autoload', '', 'no' );

		$status = wphc( 'autoload' )->is_deactivated( $name );

		$this->assertTrue( $status );

		delete_option( $name );

		wp_cache_flush();
	}

	public function test_is_core_option() {
		$this->assertFileExists( WPHC_PLUGIN_DIR . '/includes/data/wp_options.json' );

		$option = wphc( 'autoload' )->is_core_option( 'siteurl' );

		$this->assertTrue( $option );

		$option = wphc( 'autoload' )->is_core_option( 'wphc_autoload_deactivation_history' );

		$this->assertFalse( $option );
	}
}
