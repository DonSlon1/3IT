<?php namespace app;

use DbConfig;
use dibi;

class Download
   implements App
{

   public function run(){
      $url = "https://test.3it.cz/data/json";
      $content = file_get_contents($url);
      $data = json_decode($content, true);

      if (!$data) {
         throw new \Exception("Failed to fetch or parse JSON data");
      }

      DbConfig::getDbConnection();

      dibi::begin();
      try {
         foreach($data as $item){
            dibi::query("INSERT INTO `zaznamy`", [
               'jmeno' => $item['jmeno'] ?? '',
               'prijmeni' => $item['prijmeni'] ?? '',
               'datum' => $item['date'] ?? null
            ], "ON DUPLICATE KEY UPDATE", [
               'jmeno' => $item['jmeno'] ?? '',
               'prijmeni' => $item['prijmeni'] ?? '',
               'datum' => $item['date'] ?? null
            ]);
         }
         dibi::commit();
      } catch (\Exception $e) {
         dibi::rollback();
         throw $e;
      }

      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
      header("Location: " . $protocol . $_SERVER['HTTP_HOST']);
      exit;
   }
}


