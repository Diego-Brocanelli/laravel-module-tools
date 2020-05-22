<?php

declare(strict_types=1);

namespace Bnw\Tools\Commands;

use Bnw\Tools\Tools;
use Illuminate\Console\Command;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class RenameModuleCommand extends Command
{
    protected $signature = 'bnw:module:rename';

    protected $description = 'Renomeia o namespace de um módulo para um outro especificado';

    private $currentVendor;

    private $currentNamespace;

    private $currentTag;

    private $newVendor;

    private $newNamespace;

    private $newTag;

    private $newPort;

    private $rootPath;

    private $originPath;

    private $destinyPath;

    public function handle()
    {
        $this->newVendor    = 'Bueno';
        $this->newNamespace = 'FooBar';
        $this->newTag       = 'foobar';
        $this->newPort      = '2222';

        // $this->newVendor    = 'Bnw';
        // $this->newNamespace = 'Skeleton';
        // $this->newTag       = 'skeleton';
        // $this->newPort      = '2020';

        if (Tools::testRunning() === false) {
            $this->newVendor    = $this->ask('Enter the vendor name: (ex: Bnw)');
            $this->newNamespace = $this->ask('Enter the module name: (ex: FooBar)');
            $this->newTag       = $this->ask('Enter the module tag: (ex: foobar)');
            $this->newPort      = $this->ask('Enter the docker localhost port: (ex: 1180)');
        }

        $this->resolvePaths();
        $this->resolveCurrentNamespace();
        
        $this->renameModule();
    }

    private function trace(string $string)
    {
        Tools::register()->trace($string);
    }

    private function resolveCurrentNamespace() : void
    {
        $currentModule = Tools::register()->runningModule();
        
        if ($currentModule === 'none') {
            $this->trace("Não há como identificar o módulo atualmente em execução");
            $this->trace("Obtendo os namespaces do arquivo composer.json");
            $this->resolveComposerNamespace();
            return;
        }

        $this->trace("Obtendo os namespaces do arquivo de configuração do módulo");
        $this->currentVendor    = config("{$currentModule}.module_vendor");
        $this->currentNamespace = config("{$currentModule}.module_namespace");
        $this->currentTag       = config("{$currentModule}.module_tag");
    }

    private function resolvePaths() : void
    {
        $this->rootPath    = dirname(dirname(__DIR__)) . '/';
        $this->originPath  = '';
        $this->destinyPath = '';

        if (Tools::testRunning() === true) {
            $this->rootPath    = dirname(dirname(__DIR__)) . '/tests/files/';
            $this->originPath  = 'origin/';
            $this->destinyPath = 'renamed/';

            $this->trace("Executando em modo de teste");
        }

        $this->trace("Diretório de Origem: {$this->rootPath}{$this->originPath}");
        $this->trace("Diretório de Destino: {$this->rootPath}{$this->destinyPath}");
    }

    private function origin(string $filename = ''): string
    {
        return $this->originPath . ltrim($filename, '/');
    }

    private function destiny(string $filename = ''): string
    {
        return $this->destinyPath . ltrim($filename, '/');
    }

    private function resolveComposerNamespace() : void
    {
        $composer = json_decode($this->filesystem()->read($this->origin('composer.json')), true);
        $namespace = explode('\\', key($composer['autoload']['psr-4']));

        if (count($namespace) < 2) {
            throw new \RuntimeException('O namespace deste pacote é inválido. Deve ser composto por Vendor\\Namespace\\Class!');
        }

        $this->currentVendor    = $namespace[0];
        $this->currentNamespace = $namespace[1];
        $this->currentTag       = mb_strtolower($namespace[1]);
    }

    private function filesystem() : Filesystem
    {
        $adapter = new Local($this->rootPath);
        return new Filesystem($adapter);
    }

    private function renameModule()
    {
        $contents = $this->filesystem()->listContents($this->origin(), true);

        array_walk($contents, function($item) {

            if ($item['type'] === 'dir') {
                return;
            }

            if ($this->needRename($item['path']) === true) {
                $newPath = $this->replaceName($item['path']);
                $this->filesystem()->copy($item['path'], $newPath);
                $item['path'] = $newPath;
            }

            $this->replaceContent($item['path']);
        });
    }

    private function needRename(string $filename) : bool
    {
        return strpos($filename, $this->currentNamespace) !== false
            || strpos($filename, $this->currentTag) !== false;
    }

    private function replaceName(string $filename) : string
    {
        $search   = [$this->currentNamespace, $this->currentVendor, $this->currentTag, $this->originPath];
        $replace  = [$this->newNamespace, $this->newVendor, $this->newTag, $this->destinyPath];
        return str_replace($search, $replace, $filename);
    }

    private function replaceContent(string $filename) : void
    {
        $content = $this->filesystem()->read($filename);

        // Vendor\Namespace\Class
        $fullSearch  = "{$this->currentVendor}\\{$this->currentNamespace}";
        $fullReplace = "{$this->currentVendor}\\{$this->currentNamespace}";
        $content     = str_replace($fullSearch, $fullReplace, $content);

        // Namespace
        $content = str_replace($this->currentNamespace, $this->newNamespace, $content);

        // Vendor
        $content = str_replace($this->currentVendor, $this->newVendor, $content);

        // vendor\tag
        $currentMixedTag = mb_strtolower($this->currentVendor) . "/{$this->currentTag}";
        $newMixedTag     = mb_strtolower($this->newVendor) . "/{$this->newTag}";
        $content         = str_replace($currentMixedTag, $newMixedTag, $content);

        // vendor::tag
        $currentMixedTag = mb_strtolower($this->currentVendor) . "::{$this->currentTag}";
        $newMixedTag     = mb_strtolower($this->newVendor) . "::{$this->newTag}";
        $content         = str_replace($currentMixedTag, $newMixedTag, $content);

        // vendor/
        $currentVendorTag = mb_strtolower($this->currentVendor) . "/";
        $newVendorTag     = mb_strtolower($this->newVendor) . "/";
        $content          = str_replace($currentVendorTag, $newVendorTag, $content);

        // tag
        $content = str_replace($this->currentTag, $this->newTag, $content);

        $filename = str_replace($this->originPath, $this->destinyPath, $filename);
        $this->filesystem()->put($filename, $content);
    }
}