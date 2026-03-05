<?php

namespace WpMVC\Contracts;

defined( 'ABSPATH' ) || exit;

use WpMVC\App;

abstract class Provider
{
    /**
     * The application instance.
     *
     * @var App
     */
    protected App $app;

    /**
     * Create a new service provider instance.
     *
     * @param  App  $app
     * @return void
     */
    public function __construct( App $app ) {
        $this->app = $app;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    abstract public function register();

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    abstract public function boot();
}