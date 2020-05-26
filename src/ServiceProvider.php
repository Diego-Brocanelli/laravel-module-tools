<?php

declare(strict_types=1);

namespace Bnw\Tools;

use Bnw\Tools\Commands\RenameModuleCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * O serviceProvider é a forma que um modulo se comunicar com o projeto principal do Laravel.
 * Através dele é possivel personalizar o caminho das configurações, rotas, views e assets da 
 * aplicação, segmentando as funcionalidades num contexto delimitado.
 * 
 * Para mais iformações sobre módulos do Laravel,
 * leia https://laravel.com/docs/7.x/packages
 */
class ServiceProvider extends BaseServiceProvider
{
    public static function toolsCommands()
    {
        return [
            RenameModuleCommand::class
        ];
    }

    /**
     * Este método é invocado pelo Laravel apenas após todos os módulos serem registrados.
     * Veja o método register().
     * 
     * Aqui pode-se implementar tratativas específicas do modulo em questão, como invocação de 
     * classes que só existem no módulo, ou utilização de classes provenientes de outros 
     * módulos de dependência.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            // Aqui devem ser registrados quantos comandos forem necesários
            $this->commands(self::toolsCommands());
        }

        // Arquivos publicados pelo artisan:
        // Ex: php artisan vendor:publish --tag=tools --force
        $this->publishes([
            __DIR__."/Config/tools.php" => config_path("tools.php"),
        ], 'tools');
    }

    /**
     * Este método é invocado pelo Laravel no momento que o módulo é carregado.
     * Neste momento, o Kernel estará carregando todos os módulos disponíveis no diretório 
     * vendor e executando seus respectivos métodos register(). 
     * 
     * IMPORTANTE: Não coloque implementações que dependam de outros módulos neste método!
     * Como o laravel carregará os módulos de forma automatizada, não é possível determinar 
     * a ordem de execução!!
     */
    public function register()
    {
        // O 'mergeConfigFrom' junta os valores do arquivo de configuração disponíveis no módulo
        // com o o arquivo de mesmo nome, publicado no projeto principal do Laravel
        // para que não existam inconsistencias ou ausência de parâmetros usados pelo módulo
        $this->mergeConfigFrom(__DIR__.'/Config/tools.php', 'tools');

        // Disponibiliza a classe principal do módulo como um alias acessível
        // pelo namespace 'tools'
        $this->app->alias(Tools::class, 'tools');
    }
}