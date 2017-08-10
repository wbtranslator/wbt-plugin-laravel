<?php

namespace WBTranslator\PluginLaravel\Models;

use WBTranslator\Sdk;

/**
 * Class WBTranslatorAbstractionsModel
 *
 * @package WBTranslator\PluginLaravel
 */
class WBTranslatorAbstractionsModel
{
    protected $sdk;
    
    public function __construct()
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://192.168.88.149:8080/api/project/'
        ]);
    
        $config = new Sdk\Config;
        $config->setApiKey(config('wbt.api_key'));
        $config->setClient($client ?? null);
        $config->setBasePath(app()->basePath());
        $config->setBaseLocale(app()->getLocale());
        $config->setLangResourcePaths([
            app()->langPath()
        ]);
        
        $this->sdk = new Sdk\WBTranslatorSdk($config);
    }
    
    public function export()
    {
        $collection = $this->sdk->locator()->scan();
        
        if ($collection) {
            return $this->sdk->translations()->create($collection);
        }
    }
    
    public function import()
    {
        $translations = $this->sdk->translations()->all();
    
        if ($translations) {
            $this->sdk->locator()->put($translations);
        }
    }
}
