<?php

declare(strict_types=1);

namespace app;

use Dibi\Bridges\Tracy\Panel;

/**
 * Database configuration and connection management
 *
 * Provides centralized database connection handling using Dibi ORM
 * with Tracy debugging panel integration for development.
 */
class DbConfig
{

   /**
    * Get database connection with automatic setup
    *
    * Creates and configures database connection with Tracy debugging
    * panel and ensures database schema is initialized.
    *
    * @return \Dibi\Connection Configured database connection
    */
   public static function getDbConnection(): \Dibi\Connection {
      if(!isset(self::$db)){
         self::$db ??= \dibi::connect([
            'host' => $_ENV['DB_HOST'] ?? '3it_test_database',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? 'toor',
            'database' => $_ENV['DB_NAME'] ?? '3it-test',
         ]);

         \dibi::query(file_get_contents('create.sql'));

         $panel = new Panel();
         $panel->register(self::$db);
      }

      return self::$db;
   }

   public static \Dibi\Connection $db;
}
