<?php
//
//namespace WBT\PluginLaravel\Models;
//
//use WebTranslator\WebTranslator;
//use Doctrine\Common\Collections\Collection;
//
//class AbstractionImport extends AbstractionBase
//{
//    protected $translations;
//    protected $webTranslator;
//
//    public function import()
//    {
//        $model = new WBTranslatorLangModel();
//
//        $translations = $this->sdk->translations()->all();
//
//        foreach ($translations as $translation) {
//            $model->saveTranslate(
//                $translation->getTranslation(),
//                $translation->getAbstractName(),
//                $translation->getGroup(),
//                $translation->getLanguage()
//            );
//        }
//    }
//
//    public function __construct($locale = null, Collection $translations)
//    {
//        parent::__construct($locale);
//        $this->translations = $translations;
//
//    }
//
//    public function putTranslations()
//    {
//        $array = $this->toArray($this->translations);
//
//
//
//    }
//
//    protected function toArray($translations)
//    {
//        $array = [];
//
//        foreach ($translations as $translation) {
//            $key = $this->getPath($translation->getGroup(), $translation->getLanguage());
//            $value = "{$translation->getAbstractName()}" . "\"=>\"" . "{$translation->getTranslation()}"; PHP_EOL;
//
//            if (array_key_exists($key, $array)){
//                $array[$key][] = &$value;
//            }
//
//            $array[$key] = &$value;
//        }
//
//        return $array;
//    }
//
//    private function getPath(string $locale, string $group, bool $full = true): string
//    {
//        $group = explode('::', $group);
//
//        if (!$full){
//            array_pop($group);
//        }
//
//        return $this->langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR .
//            implode(DIRECTORY_SEPARATOR, $group) . ($full ? '.php' : null);
//    }
//}