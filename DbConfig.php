<?php

use Dibi\Bridges\Tracy\Panel;
use Dibi\Connection;

class DbConfig
{

   public static function getDbConnection() :Connection {
      if(!isset(self::$db)){
         self::$db ??= dibi::connect([
            'host' => '3it_test_database',
            'user' => 'root',
            'password' => 'toor',
            'database' => '3it-test',
         ]);

         dibi::query(file_get_contents('create.sql'));

         $panel = new Panel();
         $panel->register(self::$db);
      }

      return self::$db;
   }

   public static Connection $db;
}
