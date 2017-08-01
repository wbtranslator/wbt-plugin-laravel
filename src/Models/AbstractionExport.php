<?php

namespace WBTranslator\PluginLaravel\Models;

use WBTranslator as WBTranslatorSdk;

/**
 * Class AbstractionExport
 *
 * @package WBTranslator\PluginLaravel
 */
class AbstractionExport extends AbstractionBase
{
    public function abstractions(): WBTranslatorSdk\Collection
    {
        $collection = new WBTranslatorSdk\Collection;

        foreach ($this->getDataFromFile() as $group => $abstractNames) {
            foreach ((self::arrayDot($abstractNames)) as $abstractName => $originalValue) {
                if (!$abstractName) {
                    continue;
                }

                $translation = new WBTranslatorSdk\Translation;
                $translation->setGroup($group ?? '');
                $translation->setAbstractName($abstractName);
                $translation->setOriginalValue(!empty($originalValue) ? (string)$originalValue : '');

                $collection->add($translation);
            }
        }

        return $collection;
    }
    
    protected function getDataFromFile(): array
    {
        $abstractions = [];

        foreach ($this->filesystem->allFiles($this->localeDirectory) as $file) {
            $relativePath = $file->getRelativePathname();
            $absolutePath = $this->localeDirectory . $relativePath;
            $data = $this->filesystem->getRequire($absolutePath);

            if (file_exists($absolutePath)) {
                if (!empty($data) && is_array($data)) {
                    $abstractions[$this->getGroup($relativePath)] = $data;
                }
            }
        }

        return $abstractions;
    }

    protected function getGroup(string $path): string
    {
        return str_replace([DIRECTORY_SEPARATOR, '.php'], [self::GROUP_DELIMITER, ''], $path);
    }
}
