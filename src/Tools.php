<?php

declare(strict_types=1);

namespace Bnw\Tools;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Esta é a classe que permite acesso a todas as funcionalidades do módulo.
 * Ela funciona como uma API, para que o módulo seja acessível a partir de outro módulos de forma
 * facilitada e direta.
 */
class Tools
{
    const RUN_IN_LARAVEL = 'laravel';

    const RUN_IN_DOCKER = 'docker';

    const RUN_IN_MODULE = 'module';

    const RUN_IN_UNKNOWN = 'unknown';

    protected static $instance;

    public static function instance() : Tools
    {
        if (static::$instance === null) {
            static::$instance = new Tools();
        }

        return static::$instance;
    }

    protected function __construct()
    {
        // Construtor inacessível
    }

    public function register() : Register
    {
        return Register::instance();
    }

    public function testRunning() : bool
    {
        if (defined('PHPUNIT_RUN_TESTSUITE') === false) {
            define('PHPUNIT_RUN_TESTSUITE', false);
        }

        return (bool) PHPUNIT_RUN_TESTSUITE;
    }

    public function runIn() : string
    {
        $pathRoot = getcwd();

        $filesystem = $this->createFilesystem($pathRoot);

        if ($filesystem->has('composer.json') === false) {
            return self::RUN_IN_UNKNOWN;
        }

        $composer = json_decode($filesystem->read('composer.json'), true);
        if ($composer['name'] !== 'laravel/laravel') {
            return self::RUN_IN_MODULE;
        }
        
        if ($composer['name'] === 'laravel/laravel'
         && $filesystem->has('mod/') === false
         && $filesystem->has('src/') === false
        ) {
            return self::RUN_IN_LARAVEL;
        }

        return self::RUN_IN_DOCKER;
    }

    public function createFilesystem($path) : Filesystem
    {
        $adapter = new Local($path, LOCK_EX, Local::SKIP_LINKS);
        return new Filesystem($adapter);
    }
    
}
