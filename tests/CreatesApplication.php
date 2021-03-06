<?php

declare(strict_types=1);

namespace Bnw\Tools\Tests;

use Bnw\Tools\ServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Encryption\Encrypter;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // A aplicação padrão do Laravel está instalada como dependência do composer
        $app = require dirname(__DIR__).'/vendor/laravel/laravel/bootstrap/app.php';

        // Muda a localização do diretório de ambiente. 
        // Onde se encontra o .env
        $app->useEnvironmentPath(__DIR__.'/../../.docker/laravel/app/');

        $app->make(Kernel::class)->bootstrap();

        // disponibiliza este módulo para o Laravel
        $app->register(ServiceProvider::class);

        return $app;
    }
}
