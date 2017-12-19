<?php

namespace WBTranslator\PluginLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use WBTranslator\PluginLaravel\Console\Commands\AbstractionsExportCommand;
use WBTranslator\PluginLaravel\Console\Commands\AbstractionsImportCommand;

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
            AbstractionsExportCommand::class,
            AbstractionsImportCommand::class
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
