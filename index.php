<?php

use app\App;
use app\Tabulka;
use Tracy\Debugger;

require_once 'vendor/autoload.php';

Debugger::enable(Debugger::Production, __DIR__ .'/zeta/logs/');

require_once 'DbConfig.php';
require_once 'Latte.php';
require_once 'app/ErrorHandler.php';

$urlPath = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), '/');

// Default to Home page
if (empty($urlPath)) {
   $className = 'app\Home';
} else {
   $className = 'app\\' . ucfirst($urlPath);
}

// Validate class exists and implements App interface
if (!class_exists($className) || !is_a($className, App::class, true)) {
   // Fall back to Home for invalid routes
   $className = 'app\Home';
}

$app = new $className();
try {
   $app->run();
} catch (Throwable $e) {
   Debugger::log($e, Debugger::EXCEPTION);
   app\ErrorHandler::handleException($e);
}
