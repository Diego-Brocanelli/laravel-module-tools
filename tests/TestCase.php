<?php

declare(strict_types=1);

namespace Bnw\Tools\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function pathTestFiles($sufix = '')
    {
        return __DIR__ . '/' . ltrim($sufix, '/');
    }

    protected function filesystem() : Filesystem
    {
        $adapter = new Local($this->pathTestFiles('files/'));
        return new Filesystem($adapter);
    }

    protected function outputMessage(array $messages) 
    {
        array_walk($messages, function($item) {
            fwrite(STDERR, "- " . print_r($item, true) . "\n");
        });

        fwrite(STDERR, "\n");
    }
}
