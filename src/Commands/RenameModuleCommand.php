<?php

declare(strict_types=1);

namespace Bnw\Tools\Commands;

use Bnw\Tools\Tools;
use Exception;
use Illuminate\Console\Command;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RuntimeException;

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

        if ($this->tools()->testRunning() === false) {
            $this->newVendor    = $this->ask('Enter the vendor name: (ex: Bnw)');
            $this->newNamespace = $this->ask('Enter the module name: (ex: FooBar)');
            $this->newTag       = $this->ask('Enter the module tag: (ex: foobar)');
            $this->newPort      = $this->ask('Enter the docker localhost port: (ex: 1180)');
        }

        $this->resolvePaths();
        $this->resolveCurrentNamespace();
        
        $this->renameModule();
    }

    private function getModuleSrc() : string
    {
        $currentPath = getcwd();

        if ($this->tools()->runIn() === Tools::RUN_IN_DOCKER) {
            return $currentPath . '/src/';
        }

        $configModulePath = config('tools.modules_path');
        $existsModulesDir = false;
        try {
            $existsModulesDir = $this->filesystem()->has($configModulePath);
        } catch(Exception $e) {}

        if ($existsModulesDir === false) {
            throw new RuntimeException("Unable to locate the module directory ({$currentPath}/{$configModulePath})");
        }

        // TODO
        // Implementar um seletor de modulos a serem trabalhados
        //$module = $this->choice('What is module?', ['Modulo 1', 'Modulo 2']);
        throw new Exception("Module specification does not implemented");

        return '/caminho/do/modulo/';
    }

    private function tools()
    {
        return Tools::instance();
    }

    private function trace(string $string)
    {
        if ($this->tools()->testRunning() === true) {
            $this->tools()->register()->trace($string);
            return;
        }

        $this->line($string);
    }

    private function resolveCurrentNamespace() : void
    {
        $currentModule = $this->tools()->register()->runningModule();
        
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
        $this->rootPath    = $this->getModuleSrc();
        $this->originPath  = '';
        $this->destinyPath = '';

        if ($this->tools()->testRunning() === true) {
            $this->rootPath    = dirname(dirname(__DIR__)) . '/tests/files/';
            $this->originPath  = 'origin/src/';
            $this->destinyPath = 'renamed/src/';

            $this->trace("Executando em modo de teste");
        }

        $this->trace("Diretório de Origem: {$this->rootPath}{$this->originPath}");
        $this->trace("Diretório de Destino: {$this->rootPath}{$this->destinyPath}");
    }

    private function origin(string $filename = ''): string
    {
        return $this->originPath . ltrim($filename, '/');
    }

    private function resolveComposerNamespace() : void
    {
        $serviceProvider = $this->filesystem()->read($this->origin('ServiceProvider.php'));

        $matchs = [];
        preg_match('/namespace (.*);/', $serviceProvider, $matchs);
        $namespace = explode('\\', $matchs[1]);

        if (count($namespace) < 2) {
            throw new \RuntimeException('O namespace deste pacote é inválido. Deve ser composto por Vendor\\Namespace\\Class!');
        }

        $this->currentVendor    = $namespace[0];
        $this->currentNamespace = $namespace[1];
        $this->currentTag       = mb_strtolower($namespace[1]);
    }

    private function filesystem() : Filesystem
    {
        return Tools::instance()->createFilesystem($this->rootPath);
    }

    private function renameModule()
    {
        if ($this->currentNamespace === 'Tools') {
            throw new RuntimeException("The tools module cannot be renamed");
        }

        $contents = $this->filesystem()->listContents($this->origin('/'), true);

        array_walk($contents, function($item) {

            if ($item['type'] === 'dir') {
                return;
            }

            if ($this->needRename($item['path']) === true) {
                $newPath = $this->replaceName($item['path']);
                $this->filesystem()->copy($item['path'], $newPath);
                $this->info("Renomeado: {$item['path']} -> $newPath");
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