<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Los tests borran y recrean tablas (RefreshDatabase). Si por un config cacheado
        // o un .env distinto apuntaran a la base real, se detienen aquí antes de tocar nada.
        $conexion = $app['config']->get('database.default');
        $base = (string) $app['config']->get("database.connections.$conexion.database");
        if (substr($base, -5) !== '_test') {
            fwrite(STDERR, "\nTests detenidos: la base configurada es '$base' y debe terminar en _test.\n"
                . "Revisa phpunit.xml y borra bootstrap/cache/config.php si existe (php artisan config:clear).\n");
            exit(1);
        }

        return $app;
    }
}
