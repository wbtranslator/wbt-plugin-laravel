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
        
        $this->baseLang = !empty($config['locale']) ? $app->setLocale($config['locale']) : $app->getLocale();
        $this->langPath = !empty($config['lang_path']) ? $config['lang_path'] : $app->langPath();
        $this->langPath = rtrim($this->langPath, '/') . '/';
    }
    
    public function loadLocales()
    {
        $this->findLocales($this->langPath, base_path());
        $this->prepareLocales($this->unprocessedLocales);
        
        return $this->processedLocales;
    }
    
    public function saveTranslate($translate, $abstractName, $group, $language)
    {
        $file = $this->createTranslationPath($language, $group);
        $this->createTranslationFile($file, $translate, $abstractName);
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
