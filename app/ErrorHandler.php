<?php namespace app;

use Tracy\Debugger;
use Latte;

class ErrorHandler
{
    public static function handleException(\Throwable $e, string $message = null): void
    {
        Debugger::log($e, Debugger::EXCEPTION);

        $engine = Latte::getEngine();
        $engine->render(__DIR__ . '/error.latte', [
            'message' => $message ?? 'Nastala chyba při zpracování požadavku.',
            'debug' => Debugger::$productionMode === false ? $e->getMessage() : null
        ]);
        exit;
    }

    public static function handleAjaxException(\Throwable $e): void
    {
        Debugger::log($e, Debugger::EXCEPTION);

        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Nastala chyba při zpracování požadavku.',
            'debug' => Debugger::$productionMode === false ? $e->getMessage() : null
        ]);
        exit;
    }
}