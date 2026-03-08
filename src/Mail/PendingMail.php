<?php

namespace WpMVC\Mail;

defined( 'ABSPATH' ) || exit;

use WpMVC\App;
use WpMVC\Providers\MailServiceProvider;

class PendingMail {
    /**
     * The recipients of the message.
     *
     * @var array
     */
    protected $to = [];

    /**
     * The cc recipients of the message.
     *
     * @var array
     */
    protected $cc = [];

    /**
     * The bcc recipients of the message.
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * Set the recipients.
     *
     * @param string|array $users
     * @return $this
     */
    public function to( $users ) {
        $this->to = (array) $users;

        return $this;
    }

    /**
     * Set the cc recipients.
     *
     * @param string|array $users
     * @return $this
     */
    public function cc( $users ) {
        $this->cc = (array) $users;

        return $this;
    }

    /**
     * Set the bcc recipients.
     *
     * @param string|array $users
     * @return $this
     */
    public function bcc( $users ) {
        $this->bcc = (array) $users;

        return $this;
    }

    /**
     * Send a mailable synchronously.
     *
     * @param Mailable $mailable
     * @return bool
     */
    public function send( Mailable $mailable ) {
        return $mailable->to( $this->to )
            ->cc( $this->cc )
            ->bcc( $this->bcc )
            ->send();
    }

    /**
     * Push the mailable to the background queue.
     *
     * @param Mailable $mailable
     * @return void
     */
    public function queue( Mailable $mailable ) {
        $mailable->to( $this->to )
            ->cc( $this->cc )
            ->bcc( $this->bcc );

        $prefix = App::get_config()->get( 'app.hook_prefix' ) ?: 'wpmvc';
        do_action( "{$prefix}_mailable_queued", $mailable );

        $sequence = App::get_container()->get( SendQueuedMailable::class );
        $sequence->push_to_queue(
            [
                'class' => get_class( $mailable ),
                'data'  => $mailable->to_array(),
            ] 
        )->dispatch_queue();
    }
}
