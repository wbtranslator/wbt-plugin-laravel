<?php

namespace WBTranslator\PluginLaravel\Models;

use WBTranslator\Sdk;
use WBTranslator\PluginLaravel\Exceptions\WBTranslatorException;

/**
 * Class WBTranslatorAbstractionsModel
 *
 * @package WBTranslator\PluginLaravel
 */
class WBTranslatorAbstractionsModel
{
    /**
     * @var Sdk\WBTranslatorSdk
     */
    protected $sdk;
    
    /**
     * @var
     */
    protected $config;
    
    /**
     * WBTranslatorAbstractionsModel constructor.
     *
     * @throws WBTranslatorException
     */
    public function __construct()
    {
        $this->config = config('wbt');
        
        if (!$this->config['api_key']) {
            throw new WBTranslatorException('Parameter WBT_API_KEY is required', 422);
        }
        
        // Locale
        $locale = !empty($this->config['locale']) ? $this->config['locale'] : app()->getLocale();
    
        // Resource Lang Paths
        $langPaths = !empty($this->config['paths']) ? $this->config['paths'] : [];
        
        $sdkConfig = new Sdk\Config;
        $sdkConfig->setApiKey($this->config['api_key']);
        $sdkConfig->setBasePath(app()->basePath());
        $sdkConfig->setLocale($locale);
        $sdkConfig->setLangPaths($langPaths);
        
        if (!empty($this->config['delimiter'])) {
            $sdkConfig->setDelimiter($this->config['delimiter']);
        }
        
        $this->sdk = new Sdk\WBTranslatorSdk($sdkConfig);
    }
    
    // Send abstractions to WBTranslator
    public function export()
    {
        $collection = $this->sdk->locator()->scan();
        
        if ($collection) {
            return $this->sdk->translations()->create($collection);
        }
    
        return null;
    }
    
    // Get abstractions from WBTranslator and save them to lang directory
    public function import()
    {
        $translations = $this->sdk->translations()->all();
    
        if ($translations) {
            $this->sdk->locator()->put($translations);
        }
        
        return $translations;
    }
}
