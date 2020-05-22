<?php

declare(strict_types=1);

namespace Bnw\Tools;

/**
 * Esta é a classe que permite acesso a todas as funcionalidades do módulo.
 * Ela funciona como uma API, para que o módulo seja acessível a partir de outro módulos de forma
 * facilitada e direta.
 */
class Tools
{
    public static function register() : Register
    {
        return Register::instance();
    }

    public static function testRunning()
    {
        if (defined('PHPUNIT_RUN_TESTSUITE') === false) {
            define('PHPUNIT_RUN_TESTSUITE', false);
        }

        return PHPUNIT_RUN_TESTSUITE;
    }
    
}
