<?php

use app\App;
use app\DbConfig;
use app\Latte;
use app\ErrorHandler;
use Tracy\Debugger;

require_once 'vendor/autoload.php';

Debugger::enable(Debugger::Production, __DIR__ .'/zeta/logs/');

$urlPath = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), '/');

// Handle API routes
if (strpos($urlPath, 'api/') === 0) {
   $api = new \app\Api();
   $api->run();
   exit;
}

// Default to Home page
if (empty($urlPath)) {
   $className = 'app\Home';
} else {
   // For simple page routing, get first part of URL
   $pageName = explode('/', $urlPath)[0];
   $className = 'app\\' . ucfirst($pageName);
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

   // Simple error page if ErrorHandler fails
   if (class_exists('app\ErrorHandler')) {
      ErrorHandler::handleException($e);
   } else {
      echo '<h1>Server Error</h1><p>An error occurred. Please try again later.</p>';
      if (!Debugger::$productionMode) {
         echo '<pre>' . $e->getMessage() . '</pre>';
      }
   }
}
