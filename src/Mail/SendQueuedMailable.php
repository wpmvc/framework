<?php

namespace WpMVC\Mail;

defined( 'ABSPATH' ) || exit;

use WpMVC\Queue\Sequence;
use WpMVC\App;

class SendQueuedMailable extends Sequence {
    protected $action = 'mailable';

    /**
     * Initiate new background process.
     *
     */
    public function __construct() {
        $this->prefix = App::get_config()->get( 'app.hook_prefix' ) ?: 'wpmvc';
        parent::__construct();
    }

    protected function triggered_error( ?array $error ) {
        update_option( "{$this->prefix}_lock_queue", 1 );
    }

    protected function sleep_on_rest_time() {
        return false;
    }

    /**
     * Get the item.
     *
     * @param mixed $item
     * @return mixed
     */
    protected function get_item( $item ) {
        return $item;
    }

    /**
     * Perform the sequence task.
     *
     * @param array $item
     * @return bool
     */
    protected function perform_sequence_task( $item ) {
        if ( ! is_array( $item ) || empty( $item['class'] ) ) {
            return false;
        }

        $class = $item['class'];
        $data  = $item['data'] ?? [];

        if ( ! class_exists( $class ) ) {
            return false;
        }

        $mailable = new $class();

        if ( ! $mailable instanceof Mailable ) {
            return false;
        }

        $mailable->from_array( $data )->send();

        return false;
    }

    public function dispatch_queue() {
        if ( $this->is_active() ) {
            $lock = get_option( "{$this->prefix}_lock_queue", 0 );

            if ( 1 == $lock ) {
                update_option( "{$this->prefix}_lock_queue", 0 );
                $this->unlock_process()->save()->dispatch();
            }
            return;
        }

        $this->save()->dispatch();
    }
}
