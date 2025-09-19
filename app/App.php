<?php

declare(strict_types=1);

namespace app;

/**
 * Application controller interface
 *
 * All page controllers must implement this interface to provide
 * a consistent entry point for request handling.
 */
interface App
{
    /**
     * Execute the controller logic
     *
     * @return void
     */
    public function run(): void;
}