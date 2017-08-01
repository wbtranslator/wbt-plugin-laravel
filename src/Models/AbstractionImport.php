<?php

namespace WBT\PluginLaravel\Models;

use WebTranslator\Collection;

class AbstractionImport extends AbstractionBase
{
    const GROUP_DELIMITER = '::';

    public function import(Collection $translations)
    {
        foreach ($this->toArray($translations) as $directory => $files) {
            if (!file_exists($directory)) {
                $this->filesystem->makeDirectory($directory, 0755, true);
            }

            foreach ($files as $file => $values) {
                $content = $this->toString($values, ',' . PHP_EOL);
                file_put_contents($directory . DIRECTORY_SEPARATOR . $file,
                    '<?php' . PHP_EOL . PHP_EOL . 'return [' . PHP_EOL . $content . PHP_EOL . '];');
            }
        }
    }

    private function toString(array $translations, string $glue) :string
    {
        $content = '';

        foreach ($translations as $originalValue => $translation) {
            if (is_array($translation)) {
                $content .= '"' . $originalValue . '" => [' . $this->toString($translation, $glue) . ']' . $glue;
            } else {
                $content .= '"' . $originalValue . '" => "' . $translation . '"' . $glue;
            }
        }

        $content = substr($content, 0, 0 - strlen($glue));

        return $content;
    }

    private function toArray(Collection $translations) :array
    {
        $array = [];

        foreach ($translations as $translation) {
            $directory = $this->getPath($translation->getLanguage(), $translation->getGroup());
            $file = $this->getFile($translation->getGroup());

            array_set($array[$directory][$file], $translation->getAbstractName(), $translation->getTranslation());
        }

        return $array;
    }

    private function getPath(string $locale, string $group): string
    {
        $alterGroup = explode(self::GROUP_DELIMITER, $group);
        array_pop($alterGroup);

        return $this->langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR .
            implode(DIRECTORY_SEPARATOR, $alterGroup);
    }

    private function getFile(string $group) :string
    {
        $alterGroup = explode(self::GROUP_DELIMITER, $group);

        return array_last($alterGroup) . '.php';
    }
}
