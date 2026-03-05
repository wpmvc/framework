<?php

namespace WpMVC\Providers;

defined( 'ABSPATH' ) || exit;

use WpMVC\App;
use WpMVC\Contracts\Migration;
use WpMVC\Contracts\Provider;

class MigrationServiceProvider extends Provider {
    public function boot() {
        add_action( 'admin_init', [ $this, 'action_init' ], 5 );
    }

    /**
     * Fires after WordPress has finished loading but before any headers are sent.
     *
     */
    public function action_init() : void {
        $migrations = App::get_config()->get( 'app.migrations' );
        $option_key = App::get_config()->get( 'app.migration_db_option_key' );

        $executed_migrations = get_option( $option_key, [] );

        foreach ( $migrations as $key => $migration_class ) {

            if ( in_array( $key, $executed_migrations ) ) {
                continue;
            }

            $migration = App::get_container()->get( $migration_class );

            if ( ! $migration instanceof Migration ) {
                continue;
            }

            if ( $migration->execute() ) {
                $executed_migrations[] = $key;
                update_option( $option_key, $executed_migrations );
            }
            break;
        }
    }
}