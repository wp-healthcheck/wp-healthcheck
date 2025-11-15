<?php
class TransientsTest extends WP_UnitTestCase {
	public function test_cleanup_transients() {
		set_transient( 'wphc_expired_transient', 'expired', 2 );

		sleep( 4 );

		$this->assertNotFalse( wphc( 'module.transients' )->cleanup( true ) ); // only expired
		$this->assertFalse( get_transient( 'wphc_expired_transient' ) );

		$cleanup = wphc( 'module.transients' )->cleanup( false ); // all transients

		$this->assertNotFalse( $cleanup );
		$this->assertInternalType( 'int', $cleanup );
		$this->assertGreaterThan( 0, $cleanup );
	}

	public function test_transients() {
		$transients = wphc( 'module.transients' )->get();

		$this->assertInternalType( 'array', $transients );
		$this->assertGreaterThan( 0, sizeof( $transients ) );

		foreach ( $transients as $name => $size ) {
			$this->assertInternalType( 'float', $size );
		}
	}

	public function test_transients_stats() {
		$stats = wphc( 'module.transients' )->get_stats();

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
