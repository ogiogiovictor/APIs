<?php

namespace Bitfumes\Setup;

use Illuminate\Support\ServiceProvider;

class PrepareTraceServiceProvider extends ServiceProvider
{


    public function boot(){

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views', 'setup');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

    }

    public function register()
    {
        
    }
}