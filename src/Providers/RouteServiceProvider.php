<?php

namespace WpMVC\Providers;

defined( 'ABSPATH' ) || exit;

use WpMVC\Contracts\Provider;
use WpMVC\App;
use WpMVC\Routing\DataBinder;
use WpMVC\Routing\Router;
use WpMVC\Routing\Providers\RouteServiceProvider as WpMVCRouteServiceProvider;

class RouteServiceProvider extends Provider {
    /**
     * The routing provider instance.
     *
     * @var WpMVCRouteServiceProvider
     */
    protected WpMVCRouteServiceProvider $routing_provider;

    /**
     * Create a new service provider instance.
     *
     * @param  App  $app
     * @return void
     */
    public function __construct( App $app ) {
        parent::__construct( $app );
        $this->routing_provider = new class extends WpMVCRouteServiceProvider {};
    }

    /**
     * Bootstrap the routing service.
     *
     * Initializes the container, properties, and triggers the parent bootstrap.
     *
     * @return void
     */
    public function register(): void {
        $config = $this->app->get_config()->get( 'app' );

        $container = $this->app->get_container();

        $container->singleton( DataBinder::class )->singleton( Router::class );

        $this->routing_provider::set_container( $container );
        $this->routing_provider::set_properties(
            [
                'rest'                        => $config['rest_api'],
                'ajax'                        => $config['ajax_api'],
                'middleware'                  => $config['middleware'],
                'routes-dir'                  => $this->app->get_dir( "routes" ),
                'rest_response_action_hook'   => $config['rest_response_action_hook'] ?? '',
                'rest_response_filter_hook'   => $config['rest_response_filter_hook'] ?? '',
                'rest_permission_filter_hook' => $config['rest_permission_filter_hook'] ?? ''
            ] 
        );
    }

    /**
     * Bootstrap the routing service.
     *
     * Initializes the container, properties, and triggers the parent bootstrap.
     *
     * @return void
     */
    public function boot(): void {
        $this->routing_provider->boot();
    }
}