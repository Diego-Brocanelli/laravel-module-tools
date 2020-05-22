<?php

declare(strict_types=1);

namespace Bnw\Tools;

/**
 * Esta classe permite a identificação de tags em modo singleton.
 * É util para identificar o módulo atualmente em execução dentro do sistema.
 */
class Register
{
    private static $register;

    private $currentModule = 'none';

    private $stackTrace = [];

    private function __construct()
    {
        // ...
    }

    public static function instance() : Register
    {
        if (static::$register === null) {
            static::$register = new Register();
        }

        return static::$register;
    }

    /**
     * Esta string deve concidir com o nome do módulo, setado no arquivo de configuração
     * sob o atributo 'module_name'
     * 
     * @param string $moduleName
     */
    public function setModule(string $moduleName): void
    {
        $this->currentModule = $moduleName;
    }

    public function runningModule(): string
    {
        return $this->currentModule;
    }

    public function trace(string $string)
    {
        $this->stackTrace[] = $string;
    }

    public function stackTrace() : array
    {
        return $this->stackTrace;
    }
}
