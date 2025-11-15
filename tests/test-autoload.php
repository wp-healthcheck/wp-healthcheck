<?php
class AutoloadTest extends WP_UnitTestCase {
	public function test_autoload_history() {
		$options = wphc( 'module.autoload' )->get_history();

		$this->assertFalse( $options );

		$name = 'wphc_autoload_option';

		add_option( $name, 'autoload' );

		wphc( 'module.autoload' )->deactivate( $name );

		$options = wphc( 'module.autoload' )->get_history();

		$this->assertInternalType( 'array', $options );
		$this->assertGreaterThan( 0, sizeof( $options ) );

		delete_option( $name );

		wp_cache_flush();
	}

	public function test_autoload_options() {
		$options = wphc( 'module.autoload' )->get();

		$this->assertInternalType( 'array', $options );
		$this->assertGreaterThan( 0, sizeof( $options ) );

		foreach ( $options as $name => $size ) {
			$this->assertInternalType( 'float', $size );
		}
	}

	public function test_autoload_stats() {
		$stats = wphc( 'module.autoload' )->get_stats();

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

	public function test_deactivate_autoload_option() {
		$name = 'wphc_autoload_option';

		add_option( $name, 'autoload' );

		$this->assertInternalType( 'int', wphc( 'module.autoload' )->deactivate( $name ) );

		$history = wphc( 'module.autoload' )->get_history();

		$this->assertNotFalse( $history );
		$this->assertInternalType( 'array', $history );
		$this->assertArrayHasKey( $name, $history );

		delete_option( $name );

		wp_cache_flush();

		add_option( $name, 'autoload' );

		$this->assertInternalType( 'int', wphc( 'module.autoload' )->deactivate( $name, false ) );

		$history = wphc( 'module.autoload' )->get_history();

		$this->assertFalse( $history );

		$this->assertFalse( wphc( 'module.autoload' )->deactivate( $name ) );

		delete_option( $name );

		wp_cache_flush();
	}

	public function test_is_autoload_disabled() {
		$status = wphc( 'module.autoload' )->is_deactivated( 'siteurl' );

		$this->assertFalse( $status );

		$name = 'wphc_autoload_option';

		add_option( $name, 'autoload', '', 'no' );

		$status = wphc( 'module.autoload' )->is_deactivated( $name );

		$this->assertTrue( $status );

		delete_option( $name );

		wp_cache_flush();
	}

	public function test_is_core_option() {
		$this->assertFileExists( WPHC_PLUGIN_DIR . '/src/Data/wp_options.json' );

		$option = wphc( 'module.autoload' )->is_core_option( 'siteurl' );

		$this->assertTrue( $option );

		$option = wphc( 'module.autoload' )->is_core_option( 'wphc_autoload_deactivation_history' );

		$this->assertFalse( $option );
	}
}
