<?php

namespace WBTranslator\PluginLaravel\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class WBTranslatorServiceProvider
 *
 * @package WBTranslator\PluginLaravel
 */
class WBTranslatorServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        $paths = [
            $this->getConfigPath() => config_path('wbt.php')
        ];
        
        $this->publishes($paths, 'config');
        
        Route::group(['prefix' => 'wbt', 'namespace' => 'WBTranslator\PluginLaravel\Http\Controllers'], function () {
                Route::get('export', 'WBTranslatorController@export');
                Route::get('import', 'WBTranslatorController@import');
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
