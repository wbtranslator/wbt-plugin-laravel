<?php

namespace WBT\PluginLaravel\Models;

class WBTranslatorLangModel
{
    protected $unprocessedLocales = [];
    protected $processedLocales   = [];
    protected $files              = [];

    protected $baseLangDir;
    protected $baseLangsDir;
    protected $languages;

    protected $baseLang;
    protected $langPath;

    public function __construct()
    {
        $app = app();
        $config = config('wbt');

        $this->baseLang = $app->getLocale();
        $this->langPath = rtrim($app->langPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }


    public function saveTranslate($translate, $abstractName, $group, $language)
    {
        $file = $this->createTranslationPath($language, $group);
        $this->createTranslationFile($file, $translate, $abstractName);
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
                    str_replace('":', '" =>',
                        str_replace('\/', '/', $data)))));
        $data[strlen($data) - 1] = "]\n";

        return html_entity_decode($data);
    }

    protected function createTranslationPath(string $lang, string $path)
    {
        $path = explode('/', $path);
        $file = array_pop($path);
        $path = $this->langPath . $lang . '/' . implode('/', $path);

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
