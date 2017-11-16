<?php
class ServerTest extends WP_UnitTestCase {
    public function test_server_data() {
        $server_data = WP_Healthcheck::get_server_data();

        $keys = array( 'mysql', 'php', 'web', 'wp' );

        foreach ( $keys as $key ) {
            $this->assertArrayHasKey( $key, $server_data );

            if ( 'web' != $key ) {
                $this->assertNotEmpty( $server_data[ $key ] );
            }
        }
    }

    public function test_site_owner() {
        $owner = WP_Healthcheck::get_site_owner();

        $this->assertNotEmpty( $owner );

        $user = posix_getpwnam( $owner );

        $this->assertInternalType( 'array', $user );
        $this->assertArrayHasKey( 'uid', $user );
    }

    public function test_software_status() {
        $php = WP_Healthcheck::is_software_updated( 'php' );

        $requirements = get_transient( WP_Healthcheck::MIN_REQUIREMENTS_TRANSIENT );

        $this->assertNotFalse( $requirements );
        $this->assertInternalType( 'string', json_encode( $requirements ) );
        $this->assertInternalType( 'string', $php );

        $keys = array( 'mysql', 'php', 'wordpress' );

        foreach ( $keys as $key ) {
            $this->assertArrayHasKey( $key, $requirements );
        }

        $invalid = WP_Healthcheck::is_software_updated( 'invalid_software' );

        $this->assertFalse( $invalid );
    }

    public function test_wp_cron_disabled() {
        $disabled = WP_Healthcheck::is_wpcron_disabled();

        $this->assertInternalType( 'boolean', $disabled );
    }
}
