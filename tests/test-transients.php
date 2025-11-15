<?php
class TransientsTest extends WP_UnitTestCase {
	public function test_cleanup_plugin_transients() {
		wphc( 'server' )->is_updated( 'wp' ); // creates transients

		$this->assertNotFalse( wphc( 'server' )->get_requirements() );
		$this->assertNotFalse( wphc( 'server' )->get_data() );

		WP_Healthcheck::_cleanup_options( true );

		wphc()->flush(); // Clear container cache

		$requirements = wphc( 'server' )->get_requirements();
		$server_data  = wphc( 'server' )->get_data();

		$this->assertFalse( $requirements );
		$this->assertNotFalse( $server_data ); // Data is regenerated on get
	}

	public function test_cleanup_transients() {
		set_transient( 'wphc_expired_transient', 'expired', 2 );

		sleep( 4 );

		$this->assertNotFalse( wphc( 'transients' )->cleanup( true ) ); // only expired
		$this->assertFalse( get_transient( 'wphc_expired_transient' ) );

		$cleanup = wphc( 'transients' )->cleanup( false ); // all transients

		$this->assertNotFalse( $cleanup );
		$this->assertInternalType( 'int', $cleanup );
		$this->assertGreaterThan( 0, $cleanup );
	}

	public function test_transients() {
		$transients = wphc( 'transients' )->get();

		$this->assertInternalType( 'array', $transients );
		$this->assertGreaterThan( 0, sizeof( $transients ) );

		foreach ( $transients as $name => $size ) {
			$this->assertInternalType( 'float', $size );
		}
	}

	public function test_transients_stats() {
		$stats = wphc( 'transients' )->get_stats();

		$keys = [
			'count' => 'int',
			'size'  => 'float',
		];

		foreach ( $keys as $key => $type ) {
			$this->assertArrayHasKey( $key, $stats );
			$this->assertInternalType( $type, $stats[ $key ] );
		}
	}
}
