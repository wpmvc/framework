<?php

namespace WpMVC\Contracts;

interface Migration {
    public function more_than_version();

    public function execute(): bool;
}