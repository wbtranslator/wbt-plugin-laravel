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
    protected $sdk;
    
    protected $config;
    
    public function __construct()
    {
        $this->config = config('wbt');
        
        if (!$this->config['api_key']) {
            throw new WBTranslatorException('Parameter WBT_API_KEY is required', 422);
        }
        
        $sdkConfig = new Sdk\Config;
        $sdkConfig->setApiKey($this->config['api_key']);
        $sdkConfig->setClient($this->config ?? null);
        $sdkConfig->setBasePath(app()->basePath());
        $sdkConfig->setBaseLocale(!empty($this->config['locale']) ? $this->config['locale'] : app()->getLocale());
        $sdkConfig->setLangResourcePaths($this->langPaths());
        
        if (!empty($this->config['group_delimiter'])) {
            $sdkConfig->setGroupDelimiter($this->config['group_delimiter']);
        }
        
        $this->sdk = new Sdk\WBTranslatorSdk($sdkConfig);
    }
    
    public function langPaths()
    {
        $langPaths = [app()->langPath()];

        if (!empty($this->config['lang_paths'])) {
            $langPaths = array_merge($langPaths, $this->config['lang_paths']);
    
            $langPaths = array_map(function($el) {
                return rtrim($el, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }, $langPaths);
        }
        
        return $langPaths;
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
