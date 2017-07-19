<?php

namespace App\Translator\Models;

use WebTranslator\{
    Collection,
    Translation,
    WebTranslator
};

class Translator
{
    const BASE_LANG_PATH = '/resources/lang/';

    const REQUEST_SIZE = 2;
    const RECEIVE_SIZE = 1000;

    protected $client;
    protected $apiKey;

    protected $unprocessedLocales = [];
    protected $processedLocales   = [];
    protected $files              = [];

    protected $baseLangDir;
    protected $baseLangsDir;
    protected $baseLang;
    protected $languages;

    protected $webTranslator;

    public function __construct()
    {
        $this->setBaseLang(config('app.locale'));

        $this->webTranslator = new WebTranslator(env('TRANSLATOR_API_KEY'));
    }

    public function getBasePath(): string
    {
        return base_path();
    }

    public function setBaseLang($lang): self
    {
        $this->baseLang = $lang;

        return $this;
    }

    public function getBaseLang(): string
    {
        return $this->baseLang;
    }

    public function import()
    {
        $translations = $this->webTranslator->translations()->all();

        foreach ($translations as $translation) {
            $this->saveTranslate(
                $translation->getTranslation(),
                $translation->getAbstractName(),
                $translation->getGroup(),
                $translation->getLanguage()
            );
        }
    }

    public function export()
    {
        $this->loadLocales();

        $collection = new Collection();
        foreach ($this->processedLocales as $group => $abstractNames) {
            foreach ($abstractNames as $abstractName => $originalValue) {
                $translation = new Translation();
                $translation->addGroup($group);
                $translation->setAbstractName($abstractName);
                $translation->setOriginalValue($originalValue);

                $collection->add($translation);
            }
        }

        $this->webTranslator->translations()->create($collection);
    }

    protected function getBaseLangDir(): string
    {
        if (null === $this->baseLangDir) {
            $baseLang = $this->getBaseLang();

            $this->baseLangDir = $this->getBasePath() . self::BASE_LANG_PATH . $baseLang . '/';
        }

        return $this->baseLangDir;
    }

    protected function getBaseLangsDir(): string
    {
        if (null === $this->baseLangsDir) {
            $this->baseLangsDir = $this->getBasePath() . self::BASE_LANG_PATH;
        }

        return $this->baseLangsDir;
    }

    protected function loadLocales()
    {
        $path = $this->getBaseLangDir();
        $baseLocalePath = substr($path, strpos($path, 'lang'), -1);

        $this->findLocales($path, $baseLocalePath);
        $this->prepareLocales($this->unprocessedLocales);
    }

    protected function findLocales($path, $baseLocaleDir)
    {
        if (!is_dir($path) || !$files = scandir($path)) {
            return;
        }

        $_path = substr($path, strpos($path, 'lang'), -1);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir($path . '/' . $file)) {
                $this->findLocales($path . $file . '/', $baseLocaleDir);
            } else {
                $ext = explode('.', $file);
                unset($ext[count($ext) - 1]);
                $ext = implode('.', $ext);
                $k = $_path . '/' . $ext;
                $k = ltrim(str_replace($baseLocaleDir, '', $k), '/');

                $this->unprocessedLocales[$k] = require_once $path . $file;
            }
        }
    }

    protected function prepareLocales($unprocessedLocales, $path = '')
    {
        if (empty($unprocessedLocales)) {
            return;
        }

        reset($unprocessedLocales);

        do {
            if (is_array(current($unprocessedLocales))) {
                $this->prepareLocales(current($unprocessedLocales), $path . '::' . key($unprocessedLocales));
            } else {
                $k = $path . '::' . key($unprocessedLocales);
                $k = substr($k, 2);

                $group = str_replace('::', '.', substr($k, strpos($k,'::') + 2));
                $array = substr($k, 0, strpos($k,'::'));

                $this->processedLocales[$array][$group] = current($unprocessedLocales);
            }
        } while (next($unprocessedLocales));
    }

    public function saveTranslate(
        $translate,
        $abstractName,
        $group,
        $language) {
        $file = $this->createTranslationPath($language, $group);
        $this->createTranslationFile(
            $file,
            $translate,
            $abstractName
        );
    }

    protected function createTranslationFile(string $file, string $translate, string $abstractName)
    {
        $translatePath = explode('.', $abstractName);
        $translatePath[] = $translate;

        if (file_exists($file)) {
            if (!isset($this->files[$file])) {
                $this->files[$file] = require_once $file;
            }

            $data = array_replace_recursive($this->files[$file], $this->makeArray($translatePath));
        } else {
            $data = $this->makeArray($translatePath);
        }

        $this->files[$file] = $data;

        file_put_contents($file, "<?php\n\nreturn " . $this->makeTranslationContent($data) . ";");
    }

    protected function makeTranslationContent(array $data): string
    {
        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $data = str_replace("{\n", "[\n",
            str_replace("}\n", "]\n",
                str_replace("},\n", "],\n",
                    str_replace('":', '" =>', $data))));
        $data[strlen($data) - 1] = "]\n";

        return $data;
    }

    protected function createTranslationPath(string $lang, string $path): string
    {
        $path = explode('/', $path);
        $file = array_pop($path);
        $path = $this->getBaseLangsDir() . $lang . '/' . implode('/', $path);

        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        return $path . '/' . $file . '.php';
    }

    protected function makeArray(Array &$array)
    {
        if (count($array) > 1) {
            return [array_shift($array) => $this->makeArray($array)];
        } else {
            return $array[0];
        }
    }
}