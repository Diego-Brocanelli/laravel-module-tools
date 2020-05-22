<?php

declare(strict_types=1);

namespace Bnw\Tools\Tests\Feature;

use Bnw\Tools\Tests\TestCase;
use Bnw\Tools\Tools;

class RenameCommandTest extends TestCase
{
    // private function createStructure()
    // {
    //     if ($this->filesystem()->has('/renamed/composer.json') === true) {
    //         return;
    //     }

    //     $contents = $this->filesystem()->listContents('/origin', true);
    //     array_walk($contents, function($item) {

    //         $item['newPath'] = str_replace('origin', 'renamed', $item['path']);
            
    //         if ($item['type'] === 'dir' && $this->filesystem()->has($item['newPath']) === false) {
    //             $this->filesystem()->createDir($item['newPath']);
    //             return;
    //         }

    //         $this->filesystem()->copy($item['path'], $item['newPath']);
    //     });

    //     // Restabelece o arquivo .gitkeep
    //     if ($this->filesystem()->has('renamed/.gitkeep') === false) {
    //         $this->filesystem()->write('renamed/.gitkeep', '');
    //     }
        
    // }

    private function deleteStructure()
    {
        if ($this->filesystem()->has('/renamed/composer.json') === false) {
            return;
        }

        $this->filesystem()->deleteDir('/renamed/src');
        $this->filesystem()->delete('/renamed/composer.json');
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->deleteStructure();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->deleteStructure();
    }

    /** @test */
    public function basicOne()
    {
        $this->artisan('bnw:module:rename')
             ->assertExitCode(0);

        $this->outputMessage(
            array_merge(['TESTE: ' .__METHOD__], Tools::register()->stackTrace())
        );

        $contents = $this->filesystem()->listContents('/renamed', true);

        // Verifica se algum arquivo ficou sem renomear
        $result = true;
        array_walk($contents, function($item) use (&$result) {

            // Na implementação do comando, o namespace original é obtido de 
            // tests/files/origin/composer.json no atributo autoload.psr-4
            // ou do arquivo de configuração do módulo em execução
            $vendor    = 'Bnw';
            $namespace = 'Skeleton';
            $tag       = 'skeleton';

            // Verifica os nomes dos arquivos
            if (strpos($item['path'], $vendor) !== false
             || strpos($item['path'], $namespace) !== false
             || strpos($item['path'], $tag) !== false
            ) {
                $result = false;
            }

            if ($item['type'] === 'dir') {
                return;
            }

            $content = $this->filesystem()->read($item['path']);
            if (strpos($content, $vendor) !== false
             || strpos($content, $namespace) !== false
             || strpos($content, $tag) !== false
            ) {
                $result = false;
            }
            
        });

        $this->assertTrue($result);
    }
}
