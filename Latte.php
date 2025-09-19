<?php

use Latte\Bridges\Tracy\TracyExtension;
use Latte\Engine;

class Latte
{

   public static function getEngine() :Engine {
      if(!isset(self::$engine)){
         self::$engine ??= new Engine();
         self::$engine->setTempDirectory('./zeta/latte')
            ->addExtension(new TracyExtension());
      }

      return self::$engine;
   }

   private static Engine $engine;
}
