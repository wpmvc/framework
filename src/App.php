<?php

namespace WpMVC;

defined( 'ABSPATH' ) || exit;

use WpMVC\Contracts\Provider;
use WpMVC\Providers\EnqueueServiceProvider;
use WpMVC\Providers\MigrationServiceProvider;
use WpMVC\Providers\RouteServiceProvider;
use WpMVC\Providers\MailServiceProvider;
use WpMVC\Container\Container;
use WpMVC\Container\ContextualBindingBuilder;

class App
{
    protected static bool $loaded;

    public static App $instance;

    protected static Container $container;

    protected static Config $config;

    protected static string $root_dir;

    protected static string $root_url;

    public static string $plugin_root_file;

    public static function instance() {
        if ( empty( static::$instance ) ) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function get_container(): Container {
        return static::$container;
    }

    public static function get_config(): Config {
        return static::$config;
    }

    public function bind( string $abstract, $concrete = null ): self {
        static::$container->bind( $abstract, $concrete );
        return $this;
    }

    public function singleton( string $abstract, $concrete = null ): self {
        static::$container->singleton( $abstract, $concrete );
        return $this;
    }

    public function alias( string $abstract, string $alias ): self {
        static::$container->alias( $abstract, $alias );
        return $this;
    }

    public function tag( $abstracts, $tags ): self {
        static::$container->tag( $abstracts, $tags );
        return $this;
    }

    public function tagged( string $tag ): iterable {
        return static::$container->tagged( $tag );
    }

    /**
     * Resolve the given type from the container.
     *
     * @template T
     * @param  class-string<T>|string  $abstract
     * @param  array                   $parameters
     * @return T
     */
    public function make( string $abstract, array $parameters = [] ) {
        return static::$container->make( $abstract, $parameters );
    }

    /**
     * Get a service from the container.
     *
     * @template T
     * @param  class-string<T>|string  $id
     * @param  array                   $params
     * @return T
     */
    public function get( string $id, array $params = [] ) {
        return static::$container->get( $id, $params );
    }

    public function set( string $id, $instance ): self {
        static::$container->set( $id, $instance );
        return $this;
    }

    public function has( string $id ): bool {
        return static::$container->has( $id );
    }

    /**
     * Define a contextual binding.
     *
     * @param  string  $concrete
     * @return ContextualBindingBuilder
     */
    public function when( string $concrete ): ContextualBindingBuilder {
        return static::$container->when( $concrete );
    }

    public function boot( string $plugin_root_file, string $plugin_root_dir ) {
        if ( ! empty( static::$loaded ) ) {
            return;
        }

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $container = new Container();
        $container->set( static::class, static::$instance );

        $config = $container->get( Config::class );
        $container->set( Config::class, $config ); // Ensure Config is a singleton

        static::$config    = $config;
        static::$container = $container;

        $this->set_path( $plugin_root_file, $plugin_root_dir );
    }

    public function load() {
        if ( ! empty( static::$loaded ) ) {
            return;
        }

        // 1. Register Phase
        $this->register_core_service_providers();
        $this->register_plugin_service_providers();

        // 2. Boot Phase
        $this->boot_core_service_providers();
        $this->boot_plugin_service_providers();

        static::$loaded = true;
    }

    protected function set_path( string $plugin_root_file, string $plugin_root_dir ) {
        static::$plugin_root_file = $plugin_root_file;
        static::$root_url         = trailingslashit( plugin_dir_url( $plugin_root_file ) );
        static::$root_dir         = trailingslashit( $plugin_root_dir );
    }

    public static function get_dir( string $dir = '' ) {
        return static::$root_dir . ltrim( $dir, '/' );
    }

    public static function get_url( string $url = '' ) {
        return static::$root_url . ltrim( $url, '/' );
    }

    protected function register_core_service_providers(): void { 
        $this->register_service_providers( $this->core_service_providers() );
    }

    protected function register_plugin_service_providers(): void {
        $this->register_service_providers( static::$config->get( 'app.providers' ) );

        if ( is_admin() ) {
            $this->register_service_providers( static::$config->get( 'app.admin_providers' ) );
        }
    }

    protected function register_service_providers( array $providers ): void {
        foreach ( $providers as $provider ) {
            $provider_instance = new $provider( $this );

            if ( $provider_instance instanceof Provider ) {
                // Register the provider instance as a singleton for the boot phase
                static::$container->set( $provider, $provider_instance );
                $provider_instance->register();
            }
        }
    }

    protected function boot_core_service_providers(): void { 
        $this->boot_service_providers( $this->core_service_providers() );
    }

    protected function boot_plugin_service_providers(): void {
        $this->boot_service_providers( static::$config->get( 'app.providers' ) );

        if ( is_admin() ) {
            $this->boot_service_providers( static::$config->get( 'app.admin_providers' ) );
        }
    }

    protected function boot_service_providers( array $providers ): void {
        foreach ( $providers as $provider ) {

            $provider_instance = static::$container->get( $provider );

            if ( $provider_instance instanceof Provider ) {
                $provider_instance->boot();
            }
        }
    }

    protected function core_service_providers() {
        return [
            MigrationServiceProvider::class,
            RouteServiceProvider::class,
            EnqueueServiceProvider::class,
            MailServiceProvider::class
        ];
    }
}