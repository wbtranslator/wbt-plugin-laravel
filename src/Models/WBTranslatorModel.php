<?php

namespace WBT\PluginLaravel\Models;

use WebTranslator\{
    Collection,
    Translation,
    WebTranslator
};

class WBTranslatorModel
{
    protected $sdk;

    public function __construct()
    {
        /*$client = new \GuzzleHttp\Client([
            'base_uri' => 'http://192.168.88.149:8080/api/project/'
        ]);*/
        
        $this->sdk = new WebTranslator(config('wbt.api_key'), $client ?? null);
    }

    public function import()
    {
        $model = new WBTranslatorLangModel();

        $translations = $this->sdk->translations()->all();

        foreach ($translations as $translation) {
            $model->saveTranslate(
                $translation->getTranslation(),
                $translation->getAbstractName(),
                $translation->getGroup(),
                $translation->getLanguage()
            );
        }
    }

    public function export()
    {
        $model = new AbstractionExport();
        $locales = $model->getAbstractions();

        $collection = new Collection();
        foreach ($locales as $group => $abstractNames) {
            foreach ($abstractNames as $abstractName => $originalValue) {
                $translation = new Translation();
                $translation->addGroup($group);
                $translation->setAbstractName($abstractName);
                $translation->setOriginalValue($originalValue);

                $collection->add($translation);
            }
        }

//        $this->sdk->translations()->create($collection);
    }
}