<?php
class ServerTest extends WP_UnitTestCase {
	public function test_server_data() {
		$server_data = wphc( 'module.server' )->get_data();

		$keys = [ 'database', 'php', 'web', 'wp' ];

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $server_data );

			if ( 'web' !== $key ) {
				$this->assertNotEmpty( $server_data[ $key ] );
			}
		}
	}

	public function test_software_status() {
		$php = wphc( 'module.server' )->is_updated( 'php' );

		$requirements = wphc( 'module.server' )->get_requirements();

		$this->assertNotFalse( $requirements );
		$this->assertInternalType( 'string', json_encode( $requirements ) );
		$this->assertInternalType( 'string', $php );

		$keys = [ 'mariadb', 'mysql', 'php', 'wordpress' ];

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $requirements );
		}

		$invalid = wphc( 'module.server' )->is_updated( 'invalid_software' );

		$this->assertFalse( $invalid );
	}
}
