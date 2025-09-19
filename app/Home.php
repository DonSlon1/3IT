<?php

declare(strict_types=1);

namespace app;

/**
 * Home page controller
 *
 * Renders the main dashboard page with application overview,
 * feature cards, and quick navigation to other sections.
 */
class Home implements App
{
    /**
     * Render the home page
     *
     * Displays the main dashboard with application overview and navigation.
     *
     * @return void
     */
    public function run(): void
    {
        $engine = Latte::getEngine();
        $engine->render(__DIR__ . '/home.latte');
    }
}