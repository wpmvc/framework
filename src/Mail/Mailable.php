<?php

namespace WpMVC\Mail;

defined( 'ABSPATH' ) || exit;

use WpMVC\App;
use WpMVC\View\View;

abstract class Mailable {
    /**
     * The from address for the message.
     *
     * @var string
     */
    public $from_address;

    /**
     * The from name for the message.
     *
     * @var string
     */
    public $from_name;

    /**
     * The recipients of the message.
     *
     * @var array
     */
    public $to = [];

    /**
     * The recipients of the message.
     *
     * @var array
     */
    public $cc = [];

    /**
     * The recipients of the message.
     *
     * @var array
     */
    public $bcc = [];

    /**
     * The subject of the message.
     *
     * @var string
     */
    public $subject;

    /**
     * The view for the message.
     *
     * @var string
     */
    public $view;

    /**
     * The data for the view.
     *
     * @var array
     */
    public $view_data = [];

    /**
     * The attachments for the message.
     *
     * @var array
     */
    public $attachments = [];

    /**
     * Determine if the mailable should be queued.
     *
     * @var bool
     */
    public $should_queue = false;

    /**
     * Build the message.
     *
     * @return $this
     */
    abstract public function build();

    /**
     * Set the from address and name.
     *
     * @param string $address
     * @param string|null $name
     * @return $this
     */
    public function from( string $address, ?string $name = null ) {
        $this->from_address = $address;
        $this->from_name    = $name;

        return $this;
    }

    /**
     * Set the recipients.
     *
     * @param string|array $address
     * @return $this
     */
    public function to( $address ) {
        $this->to = (array) $address;

        return $this;
    }

    /**
     * Set the cc recipients.
     *
     * @param string|array $address
     * @return $this
     */
    public function cc( $address ) {
        $this->cc = (array) $address;

        return $this;
    }

    /**
     * Set the bcc recipients.
     *
     * @param string|array $address
     * @return $this
     */
    public function bcc( $address ) {
        $this->bcc = (array) $address;

        return $this;
    }

    /**
     * Set the subject.
     *
     * @param string $subject
     * @return $this
     */
    public function subject( string $subject ) {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the view.
     *
     * @param string $view
     * @param array $data
     * @return $this
     */
    public function view( string $view, array $data = [] ) {
        $this->view      = $view;
        $this->view_data = array_merge( $this->view_data, $data );

        return $this;
    }

    /**
     * Set the view data.
     *
     * @param array $data
     * @return $this
     */
    public function view_data( array $data ) {
        $this->view_data = array_merge( $this->view_data, $data );

        return $this;
    }

    /**
     * Add an attachment.
     *
     * @param string $path
     * @return $this
     */
    public function attach( string $path ) {
        $this->attachments[] = $path;

        return $this;
    }

    /**
     * Send the message.
     *
     * @return bool
     */
    public function send() {
        $this->build();

        $prefix = App::get_config()->get( 'app.hook_prefix' ) ?: 'wpmvc';

        do_action( "{$prefix}_mailable_sending", $this );

        $to      = implode( ',', $this->to );
        $subject = $this->subject;
        $message = View::get( $this->view, $this->view_data );
        
        $headers     = apply_filters( "{$prefix}_mailable_headers", $this->build_headers(), $this );
        $attachments = apply_filters( "{$prefix}_mailable_attachments", $this->attachments, $this );

        $result = wp_mail( $to, $subject, $message, $headers, $attachments );

        if ( $result ) {
            do_action( "{$prefix}_mailable_sent", $this );
        }

        return $result;
    }

    /**
     * Convert the mailable to an array.
     *
     * @return array
     */
    public function to_array() {
        return [
            'from_address' => $this->from_address,
            'from_name'    => $this->from_name,
            'to'           => $this->to,
            'cc'           => $this->cc,
            'bcc'          => $this->bcc,
            'subject'      => $this->subject,
            'view'         => $this->view,
            'view_data'    => $this->view_data,
            'attachments'  => $this->attachments,
            'should_queue' => $this->should_queue,
        ];
    }

    /**
     * Hydrate the mailable from an array.
     *
     * @param array $data
     * @return $this
     */
    public function from_array( array $data ) {
        foreach ( $data as $key => $value ) {
            if ( property_exists( $this, $key ) ) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * Build the mail headers.
     *
     * @return array
     */
    protected function build_headers() {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        if ( $this->from_address ) {
            $from = $this->from_address;
            if ( $this->from_name ) {
                $from = "{$this->from_name} <{$from}>";
            }
            $headers[] = "From: {$from}";
        }

        if ( ! empty( $this->cc ) ) {
            $headers[] = 'Cc: ' . implode( ',', $this->cc );
        }

        if ( ! empty( $this->bcc ) ) {
            $headers[] = 'Bcc: ' . implode( ',', $this->bcc );
        }

        return $headers;
    }
}
