<?php namespace app;

use DbConfig;
use dibi;
use Latte;

class Tabulka
   implements App
{

   public function run() :void {
      try {
         DbConfig::getDbConnection();

         $allowedColumns = ['id', 'jmeno', 'prijmeni', 'datum'];
         $orderBy = $_GET['order'] ?? 'datum';
         $direction = strtoupper($_GET['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

         if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'datum';
         }

         $res = dibi::query('SELECT * FROM `zaznamy` ORDER BY %n %sql', $orderBy, $direction)->fetchAll();

         $successMessage = null;
         if (isset($_GET['success'])) {
            $successMessage = match($_GET['success']) {
               'import' => 'Data byla úspěšně importována.',
               default => null
            };
         }

         $engine = Latte::getEngine();
         $engine->render(__DIR__ . '/tabulka.latte', [
            'res' => $res,
            'orderBy' => $orderBy,
            'direction' => $direction,
            'successMessage' => $successMessage
         ]);
      } catch (\Exception $e) {
         ErrorHandler::handleException($e, 'Nepodařilo se načíst data z databáze.');
      }
   }
}
