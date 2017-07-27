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
    protected $collection;

    public function __construct()
    {
        /*$client = new \GuzzleHttp\Client([
            'base_uri' => 'http://192.168.88.149:8080/api/project/'
        ]);*/

        $this->collection = new Collection();
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
        $export = new AbstractionExport();
        $locales = $export->getAbstractions();

        foreach ($locales as $group => $abstractNames) {
            $this->getAbstractionsRecursively($abstractNames, $group);
        }

        $this->sdk->translations()->create($this->collection);
    }

    private function getAbstractionsRecursively($abstractNames, $group = '')
    {
        foreach ($abstractNames as $abstractName => $originalValue) {
            if (is_array($originalValue)) {
                $this->getAbstractionsRecursively($originalValue,$group . self::SEPARATOR . $abstractName );
            } else {
                $translation = new Translation();
                $translation->addGroup($group);
                $translation->setAbstractName($abstractName);
                $translation->setOriginalValue($originalValue);

                $this->collection->add($translation);
            }
        }
    }
}