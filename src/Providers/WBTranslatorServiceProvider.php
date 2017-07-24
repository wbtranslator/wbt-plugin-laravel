<?php

namespace WBT\LaravelPlugin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WBTranslatorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $paths = [$this->getConfigPath() => config_path('wbt.php')];
        $this->publishes($paths, 'config');
        
        Route::group(['prefix' => 'translator', 'namespace' => 'WBT\LaravelPlugin\Http\Controllers'], function () {
            Route::group(['prefix' => 'api'], function () {
                Route::get('init', 'ApiController@init');
                Route::get('export', 'ApiController@export');
                Route::get('import', 'Controller@import');
            });
        
            Route::get('/', 'ApiController@index');
        });
    }
    
    public function register()
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'wbt');
    }
    
    private function getConfigPath()
    {
        return realpath(__DIR__ . '/../../') . '/config/wbt.php';
    }
}
