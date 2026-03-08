<?php

namespace WpMVC\Mail;

defined( 'ABSPATH' ) || exit;

class Mail {
    /**
     * Set the recipients.
     *
     * @param string|array $users
     * @return PendingMail
     */
    public static function to( $users ) {
        return ( new PendingMail() )->to( $users );
    }

    /**
     * Set the cc recipients.
     *
     * @param string|array $users
     * @return PendingMail
     */
    public static function cc( $users ) {
        return ( new PendingMail() )->cc( $users );
    }

    /**
     * Set the bcc recipients.
     *
     * @param string|array $users
     * @return PendingMail
     */
    public static function bcc( $users ) {
        return ( new PendingMail() )->bcc( $users );
    }

    /**
     * Send a mailable synchronously.
     *
     * @param Mailable $mailable
     * @return bool
     */
    public static function send( Mailable $mailable ) {
        return ( new PendingMail() )->send( $mailable );
    }

    /**
     * Push the mailable to the background queue.
     *
     * @param Mailable $mailable
     * @return void
     */
    public static function queue( Mailable $mailable ) {
        ( new PendingMail() )->queue( $mailable );
    }
}
