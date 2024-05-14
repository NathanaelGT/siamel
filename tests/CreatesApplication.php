<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Kernel;

trait CreatesApplication
{
    public function createApplication(): ?Application
    {
        /** @var \SimpleXMLElement $xml */
        $xml = simplexml_load_file(dirname(__DIR__) . '\\phpunit.xml');

        foreach ($xml->php->env as $env) {
            $key = (string) $env['name'];
            $value = (string) $env['value'];

            $_ENV[$key] = $value;
        }

        /** @var Application $app */
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
