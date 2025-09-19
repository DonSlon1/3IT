<?php namespace app;

use DbConfig;
use dibi;

class Download
   implements App
{

   public function run(){
      try {
         $url = "https://test.3it.cz/data/json";
         $context = stream_context_create([
            'http' => [
               'timeout' => 10,
            ]
         ]);

         $content = @file_get_contents($url, false, $context);

         if ($content === false) {
            throw new \Exception("Nepodařilo se stáhnout data ze vzdáleného zdroje");
         }

         $data = json_decode($content, true);

         if (!$data || !is_array($data)) {
            throw new \Exception("Neplatný formát dat");
         }

         DbConfig::getDbConnection();

         $imported = 0;
         $updated = 0;

         dibi::begin();
         try {
            foreach($data as $item){
               if (!isset($item['jmeno']) || !isset($item['prijmeni'])) {
                  continue;
               }

               $exists = dibi::query('SELECT id FROM `zaznamy` WHERE jmeno = %s AND prijmeni = %s',
                  $item['jmeno'], $item['prijmeni'])->fetch();

               if ($exists) {
                  dibi::query('UPDATE `zaznamy` SET', [
                     'datum' => $item['date'] ?? null
                  ], 'WHERE id = %i', $exists['id']);
                  $updated++;
               } else {
                  dibi::query('INSERT INTO `zaznamy`', [
                     'jmeno' => $item['jmeno'],
                     'prijmeni' => $item['prijmeni'],
                     'datum' => $item['date'] ?? null
                  ]);
                  $imported++;
               }
            }
            dibi::commit();
         } catch (\Exception $e) {
            dibi::rollback();
            throw $e;
         }

         $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
         header("Location: " . $protocol . $_SERVER['HTTP_HOST'] . "/?success=import");
         exit;

      } catch (\Exception $e) {
         ErrorHandler::handleException($e, $e->getMessage());
      }
   }
}


