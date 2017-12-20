<?php

namespace WBTranslator\PluginLaravel\Models;

use GuzzleHttp\Client;
use WBTranslator\Sdk;
use WBTranslator\PluginLaravel\Exceptions\WBTranslatorException;
use WBTranslator\Sdk\WBTranslatorSdk;

/**
 * Class WBTranslatorAbstractionsModel
 *
 * @package WBTranslator\PluginLaravel
 */
class WBTranslatorAbstractionsModel
{
    /**
     * @var WBTranslatorSdk
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

        if (!empty($this->config['api_url'])) {
            $sdkConfig->setClient(new Client([
                'base_uri' => $this->config['api_url']
            ]));
        }

        if (!empty($this->config['delimiter'])) {
            $sdkConfig->setDelimiter($this->config['delimiter']);
        }

        $this->sdk = new WBTranslatorSdk($sdkConfig);
    }


    /**
     * Send abstractions to WBTranslator
     *
     * @return Sdk\Collection
     */
    public function export()
    {
        $data = $this->sdk->locator()->scan();

        if (!$data['collection']->isEmpty()) {
            $data['collection'] = $this->sdk->translations()->create($data['collection'])
                ->map(function (array $item){
                    $item['name'] = str_limit($item['name'], 20);
                    $item['value'] = str_limit($item['value'], 20);

                    return $item;
                })->toArray();
        }

        return $data;
    }

    /**
     * Get abstractions from WBTranslator and save them to lang directory
     *
     * @return mixed
     */
    public function import()
    {
        $translations = $this->sdk->translations()->all();

        if ($translations) {
            $this->sdk->locator()->put($translations);
        }

        return $translations->map(function (Translation $item) {
            $item->setAbstractName(str_limit($item->getAbstractName(), 20));
            $item->setOriginalValue(str_limit($item->getOriginalValue(), 20));
            $item->setTranslation(str_limit($item->getTranslation(), 20));
            $item->setComment(str_limit($item->getComment(), 20));
            $item->removeGroup();

            return (array)$item;
        })->toArray();
    }

    public function sdk(): WBTranslatorSdk
    {
        return $this->sdk;
    }
}
