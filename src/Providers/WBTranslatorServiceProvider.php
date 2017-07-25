<?php

namespace WBT\PluginLaravel\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WBTranslatorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $paths = [$this->getConfigPath() => config_path('wbt.php')];
        $this->publishes($paths, 'config');
        
        Route::group(['prefix' => 'translator', 'namespace' => 'WBT\PluginLaravel\Http\Controllers'], function () {
            Route::group(['prefix' => 'api'], function () {
                //Route::get('init', 'ApiController@init');
                Route::get('export', 'ApiController@export');
                Route::get('import', 'ApiController@import');
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
        return __DIR__ . '/../Z:/';
    }
}
