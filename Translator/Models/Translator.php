<?php

namespace App\Translator\Models;

use GuzzleHttp\Client as HttpClient;
use App\Translator\Exceptions\TranslatorException;

class Translator
{
    //const API_URL = 'http://fnukraine.pp.ua/api/v2/';
    const API_URL = 'http://192.168.88.149:8080/api/v2/';
    const BASE_LANG_PATH = '/resources/lang/';

    const REQUEST_SIZE = 100;
    const RECEIVE_SIZE = 1000;

    protected $client;
    protected $apiKey;

    protected $unprocessedLocales = [];
    protected $processedLocales   = [];
    protected $files              = [];

    protected $baseLangDir;
    protected $baseLang;
    protected $languages;

    public function __construct($apiKey = null)
    {
        $client = new HttpClient([
            'base_uri' => self::API_URL
        ]);

        $this->setClient($client);
        $this->setApiKey($apiKey);
    }

    public function setClient($client = null)
    {
        $this->client = $client;

        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getApiKey()
    {
        if (!$this->apiKey) {
            throw new TranslatorException('TRANSLATOR_API_KEY not exists!');
        }

        return $this->apiKey;
    }

    public function getBasePath()
    {
        return base_path();
    }

    public function setBaseLang($lang)
    {
        $this->baseLang = $lang;

        return $this;
    }

    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    public function getBaseLang()
    {
        return $this->baseLang;
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function init()
    {
        $response = $this->getClient()->get('project?api_key=' . $this->getApiKey());

        if ($response->getBody()) {
            $response = json_decode($response->getBody());

            if (!empty($response->data)) {
                if (isset($response->data->language)) {
                    $this->setBaseLang($response->data->language);
                }
                if (isset($response->data->languages)) {
                    $this->setLanguages($response->data->languages);
                }
            }
        }

        return [
            'base_lang' => $this->getBaseLang(),
            'languages' => $this->getLanguages(),
        ];
    }

    public function export()
    {
        $this->init();
        $this->locales();

        $url = 'project/tasks/create?api_key=' . $this->getApiKey();

        $processedLocales = array_chunk($this->processedLocales, self::REQUEST_SIZE, true);
        $locales = [];

        foreach($processedLocales as $arrLocales) {
            $pack = [];
            foreach($arrLocales as $k => $v) {
                $pack[] = [
                    'name' => $k,
                    'value' => $v
                ];
            }
            $locales[]['data'] = $pack;
        }

        $result = 0;

        if (!empty($locales)) {
            foreach ($locales as $locale) {
                $res = $this->getClient()->post($url, [
                    'form_params' => $locale
                ]);
                $res = json_decode($res->getBody());
                if (!empty($res->data->count)) {
                    $result += $res->data->count;
                }
            }
        }

        return $result;
    }

    public function import()
    {
        $this->init();

        $baseLang = $this->getBaseLang();
        $languages = $this->getLanguages();

        $result =[];

        foreach ($languages as $language) {
            if($language->id === $baseLang->id) {
                continue;
            }

            $url = 'project/translations/' . $language->id . '?limit=' . self::RECEIVE_SIZE . '&api_key=' . $this->getApiKey();
            $response = $this->getClient()->get($url);
            $body = $response->getBody();

            if ($body) {
                $body = json_decode($body);

                if (!empty($body->data->data)) {
                    $data = $body->data->data;

                    if (!$data = array_filter($data, function($v) {
                        return !empty($v->translation);
                    })) {
                        continue;
                    }

                    $result[$language->code] = 0;

                    foreach ($data as $d) {
                        $d->name = preg_replace('/\/(' . $baseLang->code . ')/', '/' . $language->code, $d->name);
                        $this->saveTranslate($d->name, $d->translation->value);
                        $result[$language->code]++;
                    }
                }
            }
        }

        return $result;
    }

    public function getBaseLangDir()
    {
        if (null === $this->baseLangDir) {
            $baseLang = $this->getBaseLang();

            if (empty($baseLang->code)) {
                throw new TranslatorException('BaseLang not exists!');
            }

            $this->baseLangDir = $this->getBasePath() . self::BASE_LANG_PATH . $baseLang->code . '/';
        }
        return $this->baseLangDir;
    }

    protected function locales($prepare = true)
    {
        $path = $this->getBaseLangDir();
        $this->loadLocales($path);

        if ($prepare) {
            $this->prepareLocales($this->unprocessedLocales);
            return $this->processedLocales;
        }

        return $this->unprocessedLocales;
    }

    protected function loadLocales($path)
    {
        if(!is_dir($path)) {
            return false;
        }

        if(!$files = scandir($path)) {
            return null;
        }

        $_path = substr($path, strpos($path, 'lang'), -1);

        do {
            if(current($files) === '.' || current($files) === '..') {
                continue;
            }

            if(is_dir($this->baseLangDir . current($files))) {
                $this->loadLocales($path . current($files) . '/');
            } else {
                $this->unprocessedLocales[$_path][current($files)] = require_once $path . current($files);
            }
        } while(next($files));
    }

    protected function prepareLocales($unprocessedLocales, $path = '')
    {
        if(empty($unprocessedLocales)) {
            return false;
        }

        reset($unprocessedLocales);

        do {
            if(is_array(current($unprocessedLocales))) {
                $this->prepareLocales(current($unprocessedLocales), $path . '::' . key($unprocessedLocales));
            } else {
                $this->processedLocales[$path . '::' . key($unprocessedLocales)] = current($unprocessedLocales);
            }
        } while(next($unprocessedLocales));
    }

    protected function saveTranslate($name, &$translate)
    {
        $data = explode('::', $name);

        $path = $data[1];
        $file = $data[2];

        unset($data[0], $data[1], $data[2]);

        $data = array_values($data);

        $path = $this->createTranslatePath($path);
        $this->createTranslateFile($file, $path, $data, $translate);
    }

    protected function createTranslatePath($unprocessedPath)
    {
        $unprocessedPath = explode('/', $unprocessedPath);

        $path = $this->getBasePath() . '/resources';

        if (!is_writable($path . '/lang')) {
            throw new \Exception('Folder "' . $path . '" must be writable!');
        }

        do {
            $path .=  '/' . current($unprocessedPath);
            if(!is_dir($path)) {
                mkdir($path);
            }
        } while(next($unprocessedPath));

        return $path;
    }

    protected function createTranslateFile($fileName, $path, $array, $translate)
    {
        array_push($array, $translate);

        if (file_exists($path . '/' . $fileName)) {
            if (!isset($this->files[$path . '/' . $fileName])) {
                $this->files[$path . '/' . $fileName] = require_once $path . '/' . $fileName;
            }

            $data = array_replace_recursive($this->files[$path . '/' . $fileName], $this->makeArray($array));
        } else {
            $data = $this->makeArray($array);
        }

        $this->files[$path . '/' . $fileName] = $data;

        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $data = str_replace("{\n", "[\n",
                str_replace("}\n", "]\n",
                str_replace("},\n", "],\n",
                str_replace('":', '" =>', $data))));
        $data[strlen($data) - 1] = "]";
        $data = trim(str_replace('    ', "\t", $data));

        if (!is_writable($path)) {
            throw new \Exception('Folder "' . $path . '" must be writable!');
        }

        file_put_contents($path . '/' . $fileName, "<?php\n\nreturn " . $data . ";");
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