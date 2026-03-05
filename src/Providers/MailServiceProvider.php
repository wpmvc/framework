<?php

namespace WpMVC\Providers;

defined( 'ABSPATH' ) || exit;

use WpMVC\Contracts\Provider;
use WpMVC\Mail\SendQueuedMailable;

class MailServiceProvider extends Provider {
    public function register() {
        $this->app->get( SendQueuedMailable::class );
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot() {}
}
