<?php namespace app;

use DbConfig;
use dibi;
use Latte;

class Tabulka
   implements App
{

   public function run() :void {
      DbConfig::getDbConnection();
      $orderBy = $_GET['order'] ?? 'datum';
      $res = dibi::query('SELECT * FROM `zaznamy` ORDER BY ' . $orderBy)->fetchAll();

      $engine = Latte::getEngine();
      $engine->render(__DIR__ . '/tabulka.latte', ['res' => $res]);
   }
}
