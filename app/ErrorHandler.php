<?php

declare(strict_types=1);

namespace app;

use Tracy\Debugger;

/**
 * Centralized error handling for both web and AJAX requests
 *
 * Provides consistent error handling with proper logging and user-friendly
 * error messages while respecting production/development mode settings.
 */
class ErrorHandler
{
    /**
     * Handle exceptions for regular web requests
     *
     * @param \Throwable $e The exception to handle
     * @param string|null $message Custom error message for user display
     * @return void
     */
    public static function handleException(\Throwable $e, string $message = null): void
    {
        Debugger::log($e, Debugger::EXCEPTION);

        $engine = Latte::getEngine();
        $engine->render(__DIR__ . '/error.latte', [
            'message' => $message ?? 'An error occurred while processing your request.',
            'debug' => Debugger::$productionMode === false ? $e->getMessage() : null
        ]);
        exit;
    }

    /**
     * Handle exceptions for AJAX requests with JSON response
     *
     * @param \Throwable $e The exception to handle
     * @return void
     */
    public static function handleAjaxException(\Throwable $e): void
    {
        Debugger::log($e, Debugger::EXCEPTION);

        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while processing your request.',
            'debug' => Debugger::$productionMode === false ? $e->getMessage() : null
        ], JSON_THROW_ON_ERROR);
        exit;
    }
}