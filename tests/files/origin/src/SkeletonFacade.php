<?php

namespace Bnw\Skeleton;

use Illuminate\Support\Facades\Facade;

/**
 * O Facade (Padrão Fachada) é um artifício usado pelo Laravel para acessar diretamente
 * as classes através de seu alias.
 * 
 * Os alias são definidos no método register() do ServiceProvider, como no exemplo a seguir:
 * $this->app->alias(Skeleton::class, 'skeleton');
 * 
 * Para mais informações, veja /src/SkeletonServiceProvider.php
 */
class SkeletonFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        // aqui, deve-se retornar a mesma string definida no registro do módulo .
        // Ex: $this->app->alias(Skeleton::class, 'skeleton');
        return 'skeleton';
    }
}
