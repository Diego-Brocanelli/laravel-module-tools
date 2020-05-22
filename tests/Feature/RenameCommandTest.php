<?php

namespace Bnw\Skeleton\Tests\Feature;

use Bnw\Skeleton\Tests\TestCase;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

define('TEST_MODE', true);

class RenameCommandTest extends TestCase
{
    private $testFiles = '/tests/.files';

    private function createStructure($testFilesPath)
    {
        if ($this->filesystem()->has($testFilesPath) === true) {
            return;
        }

        $this->filesystem()->createDir($testFilesPath);

        $contents = $this->filesystem()->listContents('/src', true);
        array_walk($contents, function($item) use ($testFilesPath) {

            if ($item['type'] === 'dir') {
                $this->filesystem()->createDir($item['path']);
                return;
            }

            $newFile = str_replace('/src/', '', $item['path']);
            $this->filesystem()->copy($item['path'], "{$testFilesPath}/{$newFile}");
        });

        $this->filesystem()->copy('/composer.json', "{$testFilesPath}/composer.json");
    }

    private function deleteStructure($testFilesPath)
    {
        if ($this->filesystem()->has($testFilesPath) === false) {
            return;
        }

        $contents = $this->filesystem()->listContents($testFilesPath, true);

        array_walk($contents, function($item) {

            if ($item['type'] === 'dir') {
                return;
            }

            $this->filesystem()->delete($item['path']);
        });

        array_walk($contents, function($item) {
            $this->filesystem()->delete($item['path']);
        });
    }

    private function filesystem() : Filesystem
    {
        $adapter = new Local(__DIR__ . '/../../');
        return new Filesystem($adapter);
    }

    /** @test */
    public function basicOne()
    {
        $this->createStructure($this->testFiles);

        $this->artisan('bnw:module-rename')
            // ->expectsQuestion('What is your name?', 'Taylor Otwell')
            // ->expectsQuestion('Which language do you program in?', 'PHP')
            // ->expectsOutput('Your name is Taylor Otwell and you program in PHP.')
            ->assertExitCode(0);

        $this->deleteStructure($this->testFiles);
    }
}
