<?php

declare(strict_types=1);

namespace Bnw\Tools\Commands;

use Illuminate\Console\Command;

use Illuminate\Console\Command;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class RenameModuleCommand extends Command
{
    protected $signature = 'bnw:module:rename';

    protected $description = 'Renomeia o namespace de um módulo para um outro especificado';

    private $vendorNamespace;

    private $moduleNamespace;

    private $tagNamespace;

    private $newNamespace;

    private $newPort;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->newNamespace = 'FooBar';
        $this->newPort      = '2222';
        // $name = $this->ask('Enter the module name: (ex: FooBar)');
        // $port = $this->ask('Enter the composer port: (ex: 1180)');

        $vendorNamespace = $this->getVendorNamespace();
        $moduleNamespace  = $this->getModuleNamespace();
        $tagNamespace    = $this->getModuleNamespace();

        $contents = $this->filesystem()->listContents('/src', true);

        array_walk($contents, function($item) {

            if ($item['type'] === 'dir') {
                return;
            }

            $this->searchReplace($item['path']);

            if ($this->needRename($item['path']) === true) {
                $newPath = $this->replaceName($item['path']);
                $this->filesystem()->rename($item['path'], $newPath);
            }
        });
        exit;//dd($contents);
        // foreach($contents as $item) {
        //     var_dump($item);
        // }
        // $response = $filesystem->update($path, $contents [, $config]);

        
        

        

        
    }

    private function filesystem() : Filesystem
    {
        $path = defined('TEST_MODE') === true
            ? dirname(__DIR__) . '/../tests/.files/'
            : dirname(dirname(__DIR__));

        $adapter = new Local($path);
        return new Filesystem($adapter);
    }

    private function needRename(string $filename) : bool
    {
        $moduleNamespace = $this->getModuleNamespace();
        return strpos($filename, $moduleNamespace) !== false;
    }

    private function replaceName(string $filename) : string
    {
        $moduleNamespace = $this->getModuleNamespace();
        return str_replace($moduleNamespace, $this->newNamespace, $filename);
    }

    private function searchReplace(string $filename) : string
    {
        $search = ['Skeleton', 'skeleton'];
        $replace = [$this->moduleNamespace(), $this->tagNamespace()];
        // trocar no conteudo
        return '';
    }

    private function getModuleNamespace() : string
    {
        if ($this->moduleNamespace !== null) {
            return $this->moduleNamespace;
        }

        $composer = json_decode($this->filesystem()->read('/composer.json'), true);

        $namespace = explode('\\', key($composer['autoload']['psr-4']));

        $this->vendorNamespace = $namespace[0];
        $this->moduleNamespace = $namespace[1];
        $this->tagNamespace    = mb_strtolower($this->moduleNamespace);

        return $this->moduleNamespace;

    }

    private function getTagNamespace() : string
    {
        if ($this->tagNamespace !== null) {
            return $this->tagNamespace;
        }

        $this->getModuleNamespace();
        return $this->tagNamespace;
    }

    private function getVendorNamespace() : string
    {
        if ($this->vendorNamespace !== null) {
            return $this->vendorNamespace;
        }

        $this->getModuleNamespace();
        return $this->vendorNamespace;
    }


}