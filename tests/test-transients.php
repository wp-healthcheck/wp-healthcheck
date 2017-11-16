<?php
class TransientsTest extends WP_UnitTestCase {
    public function test_cleanup_plugin_transients() {
        WP_Healthcheck::is_software_updated( 'wp' ); // creates both transients

        $this->assertNotFalse( get_transient( WP_Healthcheck::MIN_REQUIREMENTS_TRANSIENT ) );
        $this->assertNotFalse( get_transient( WP_Healthcheck::SERVER_DATA_TRANSIENT ) );

        WP_Healthcheck::_cleanup_options( true );

        $requirements = get_transient( WP_Healthcheck::MIN_REQUIREMENTS_TRANSIENT );
        $server_data = get_transient( WP_Healthcheck::SERVER_DATA_TRANSIENT );

        $this->assertFalse( $requirements );
        $this->assertFalse( $server_data );
    }

    public function test_cleanup_transients() {
        set_transient( 'wphc_expired_transient', 'expired', 2 );

        sleep( 4 );

        $this->assertNotFalse( WP_Healthcheck::cleanup_transients( true ) ); // only expired
        $this->assertFalse( get_transient( 'wphc_expired_transient' ) );

        $cleanup = WP_Healthcheck::cleanup_transients( false ); // all transients

        $this->assertNotFalse( $cleanup );
        $this->assertInternalType( 'int', $cleanup );
        $this->assertGreaterThan( 0, $cleanup );
    }

    public function test_transients() {
        $transients = WP_Healthcheck::get_transients();

        $this->assertInternalType( 'array', $transients );
        $this->assertGreaterThan( 0, sizeof( $transients ) );

        foreach ( $transients as $name => $size ) {
            $this->assertInternalType( 'float', $size );
        }
    }

    public function test_transients_stats() {
        $stats = WP_Healthcheck::get_transients_stats();

        $keys = array(
            'count' => 'int',
            'size'  => 'float',
        );

        foreach ( $keys as $key => $type ) {
            $this->assertArrayHasKey( $key, $stats );
            $this->assertInternalType( $type, $stats[ $key ] );
        }
    }
}
