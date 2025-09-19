<?php

use app\App;
use app\Tabulka;
use Tracy\Debugger;

require_once 'vendor/autoload.php';

Debugger::enable(Debugger::Production, __DIR__ .'/zeta/logs/');

require_once 'DbConfig.php';
require_once 'Latte.php';

$urlPath = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), '/');
$className = $urlPath ? 'app\\' . ucfirst($urlPath) : Tabulka::class;

if (!class_exists($className) || !is_a($className, App::class, true)) {
   $className = Tabulka::class;
}

$app = new $className();
try {
   $app->run();
} catch (Throwable $e) {
   Debugger::log($e, Debugger::EXCEPTION);
   app\ErrorHandler::handleException($e);
}
