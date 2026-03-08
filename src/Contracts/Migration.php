<?php

namespace WpMVC\Contracts;

defined( 'ABSPATH' ) || exit;

interface Migration {
    public function execute(): bool;
}