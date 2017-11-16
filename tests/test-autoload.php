<?php
class AutoloadTest extends WP_UnitTestCase {
    public function test_autoload_history() {
        $options = WP_Healthcheck::get_autoload_history();

        $this->assertFalse( $options );

        $name = 'wphc_autoload_option';

        add_option( $name, 'autoload' );

        WP_Healthcheck::deactivate_autoload_option( $name );

        $options = WP_Healthcheck::get_autoload_history();

        $this->assertInternalType( 'array', $options );
        $this->assertGreaterThan( 0, sizeof( $options ) );

        delete_option( $name );

        wp_cache_flush();
    }

    public function test_autoload_options() {
        $options = WP_Healthcheck::get_autoload_options();

        $this->assertInternalType( 'array', $options );
        $this->assertGreaterThan( 0, sizeof( $options ) );

        foreach ( $options as $name => $size ) {
            $this->assertInternalType( 'float', $size );
        }
    }

    public function test_autoload_stats() {
        $stats = WP_Healthcheck::get_autoload_stats();

        $this->assertInternalType( 'array', $stats );
        $this->assertGreaterThan( 0, sizeof( $stats ) );

        $keys = array(
            'count' => 'int',
            'size'  => 'float',
        );

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

        $this->assertInternalType( 'int', WP_Healthcheck::deactivate_autoload_option( $name ) );

        $option = get_option( WP_Healthcheck::DISABLE_AUTOLOAD_OPTION );

        $this->assertNotFalse( $option );
        $this->assertInternalType( 'array', $option );
        $this->assertArrayHasKey( $name, $option );

        delete_option( WP_Healthcheck::DISABLE_AUTOLOAD_OPTION );
        delete_option( $name );

        wp_cache_flush();

        add_option( $name, 'autoload' );

        $this->assertInternalType( 'int', WP_Healthcheck::deactivate_autoload_option( $name, false ) );

        $option = get_option( WP_Healthcheck::DISABLE_AUTOLOAD_OPTION );

        $this->assertFalse( $option );

        $this->assertFalse( WP_Healthcheck::deactivate_autoload_option( $name ) );

        delete_option( $name );

        wp_cache_flush();
    }

    public function test_is_autoload_disabled() {
        $status = WP_Healthcheck::is_autoload_disabled( 'siteurl' );

        $this->assertFalse( $status );

        $name = 'wphc_autoload_option';

        add_option( $name, 'autoload', '', 'no' );

        $status = WP_Healthcheck::is_autoload_disabled( $name );

        $this->assertTrue( $status );

        delete_option( $name );

        wp_cache_flush();
    }

    public function test_is_core_option() {
        $this->assertFileExists( WPHC_INC_DIR . '/data/wp_options.json' );

        $option = WP_Healthcheck::is_core_option( 'siteurl' );

        $this->assertTrue( $option );

        $option = WP_Healthcheck::is_core_option( WP_Healthcheck::DISABLE_AUTOLOAD_OPTION );

        $this->assertFalse( $option );
    }
}
