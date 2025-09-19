<?php namespace app;

use Latte;

class Home implements App
{
    public function run(): void
    {
        $engine = Latte::getEngine();
        $engine->render(__DIR__ . '/home.latte');
    }
}