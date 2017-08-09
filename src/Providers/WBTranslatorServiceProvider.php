<?php

namespace WBTranslator\PluginLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use WBTranslator\PluginLaravel\Console\Commands\AbstractionsExport;
use WBTranslator\PluginLaravel\Console\Commands\AbstractionsImport;

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

        $this->commands([
            AbstractionsExport::class,
            AbstractionsImport::class
        ]);

        $this->publishes($paths, 'config');
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
