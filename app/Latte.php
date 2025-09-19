<?php

declare(strict_types=1);

namespace app;

use Latte\Bridges\Tracy\TracyExtension;
use Latte\Engine;

/**
 * Latte template engine configuration
 *
 * Provides centralized Latte template engine setup with Tracy
 * debugging integration and shared engine instance management.
 */
class Latte
{
    /**
     * Get configured Latte template engine instance
     *
     * Returns a singleton instance of Latte engine with Tracy
     * debugging extension and proper temp directory configuration.
     *
     * @return Engine Configured Latte template engine
     */
    public static function getEngine(): Engine
    {
        if (!isset(self::$engine)) {
            self::$engine ??= new Engine();
            self::$engine->setTempDirectory('./zeta/latte')
                ->addExtension(new TracyExtension());
        }

        return self::$engine;
    }

    private static Engine $engine;
}
