<?php namespace app;

use DbConfig;
use dibi;
use Latte;

require_once __DIR__ . '/ErrorHandler.php';

class Tabulka
   implements App
{

   public function run() :void {
      try {
         DbConfig::getDbConnection();

         // Start session to get marked records
         if (!session_id()) {
            session_start();
         }
         $sessionId = session_id();

         $allowedColumns = ['id', 'jmeno', 'prijmeni', 'datum'];
         $orderBy = $_GET['order'] ?? 'datum';
         $direction = strtoupper($_GET['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

         if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'datum';
         }

         // Get records with marking info
         $res = dibi::query('
            SELECT z.*,
                   IF(m.id IS NOT NULL, 1, 0) as is_marked
            FROM `zaznamy` z
            LEFT JOIN `marked_records` m
                ON z.id = m.zaznam_id
                AND m.session_id = %s
            ORDER BY %n %sql',
            $sessionId, $orderBy, $direction
         )->fetchAll();

         // Get total count
         $totalCount = dibi::query('SELECT COUNT(*) FROM `zaznamy`')->fetchSingle();
         $markedCount = dibi::query('
            SELECT COUNT(DISTINCT zaznam_id)
            FROM `marked_records`
            WHERE session_id = %s', $sessionId)->fetchSingle();

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
            'successMessage' => $successMessage,
            'totalCount' => $totalCount,
            'markedCount' => $markedCount
         ]);
      } catch (\Exception $e) {
         ErrorHandler::handleException($e, 'Nepodařilo se načíst data z databáze.');
      }
   }
}
