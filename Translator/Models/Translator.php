<?php

namespace Translator\Models;

class Translator
{
    protected function saveTranslate(Array &$data, &$translate)
    {
        $path = $data[1];
        $file = $data[2];

        unset($data[0], $data[1], $data[2]);

        $data = array_values($data);

        $path = $this->createTranslatePath($path);
        $this->createTranslateFile($file, $path, $data, $translate);
    }

    protected function makeArray(Array &$array) {
        if(count($array) > 1)
            return [array_shift($array) => $this->makeArray($array)];
        else
            return $array[0];
    }

    protected function createTranslateFile($fileName, $path, $array, $translate)
    {
        array_push($array, $translate);
        if(file_exists($path . '/' . $fileName)) {
            if( ! isset($this->files[$path . '/' . $fileName]))
                $this->files[$path . '/' . $fileName] = require_once $path . '/' . $fileName;

            $data = array_replace_recursive($this->files[$path . '/' . $fileName], $this->makeArray($array));
        } else
            $data = $this->makeArray($array);

        $this->files[$path . '/' . $fileName] = $data;

        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $data = str_replace("{\n", "[\n",
            str_replace("}\n", "]\n",
                str_replace("},\n", "],\n",
                    str_replace('":', '" =>', $data))));
        $data[strlen($data) - 1] = "]";
        $data = trim(str_replace('    ', "\t", $data));
        file_put_contents($path . '/' . $fileName, "<?php\n\nreturn " . $data . ";");
    }

    private function createTranslatePath($unprocessedPath)
    {
        $unprocessedPath = explode('/', $unprocessedPath);
        $path = $this->basePath . '/resources';

        do {
            $path.=  '/' . current($unprocessedPath);
            if( ! is_dir($path))
                mkdir($path);
        } while(next($unprocessedPath));

        return $path;
    }

    protected function loadLocales()
    {
        if( ! is_dir($this->baseLangDir))
            return false;

        if( ! $files = scandir($this->baseLangDir))
            return null;

        $_path = substr($this->baseLangDir, strpos($this->baseLangDir, 'lang'), -1);

        do {
            if(current($files) === '.' || current($files) === '..')
                continue;

            if(is_dir($this->baseLangDir . current($files)))
                $this->loadLocales($path . current($files) . '/');
            else
                $this->unprocessedLocales[$_path][current($files)] = require_once $this->baseLangDir . current($files);
        } while(next($files));
    }

    protected function prepareLocales($unprocessedLocales, $path = '')
    {
        if(empty($unprocessedLocales))
            return false;

        reset($unprocessedLocales);
        do {
            if(is_array(current($unprocessedLocales)))
                $this->prepareLocales(current($unprocessedLocales), $path . '::' . key($unprocessedLocales));
            else
                $this->processedLocales[$path . '::' . key($unprocessedLocales)] = current($unprocessedLocales);
        } while(next($unprocessedLocales));
    }
}