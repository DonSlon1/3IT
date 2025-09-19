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

      DbConfig::getDbConnection();

      foreach($data as $item){
         dibi::query("INSERT INTO `zaznamy` (`jmeno`, `prijmeni`, `datum`) VALUES ('{$item['jmeno']}', '{$item['prijmeni']}', '{$item['date']}')");
      }

      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
      header("Location: " . $protocol . $_SERVER['HTTP_HOST']);
   }
}


