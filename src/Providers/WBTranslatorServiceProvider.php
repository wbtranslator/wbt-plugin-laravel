<?php

namespace WBT\PluginLaravel\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use WBTranslator\PluginLaravel\Console\Commands\AbstractionsExport;

class WBTranslatorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $paths = [$this->getConfigPath() => config_path('wbt.php')];
        $this->commands([AbstractionsExport::class]);
        $this->publishes($paths, 'config');

        Route::group(['prefix' => 'wbt', 'namespace' => 'WBT\PluginLaravel\Http\Controllers'], function () {
                Route::get('export', 'WBTController@export');
                Route::get('import', 'WBTController@import');
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
