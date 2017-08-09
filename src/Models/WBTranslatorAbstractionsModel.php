<?php

namespace WBTranslator\PluginLaravel\Models;

use WBTranslator as WBTranslatorSdk;

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
        
        $this->sdk = new WBTranslatorSdk(config('wbt.api_key'), $client);
    }
    
    public function export()
    {
        $collection = new WBTranslatorSdk\Collection;

        $result = $this->sdk->translations()->create($collection);
        
        return $result;
    }
    
    public function import()
    {
        $this->info('Process ... ');
        
        $translations = $this->sdk->translations()->all();
        
        return $translations;
    }
}
