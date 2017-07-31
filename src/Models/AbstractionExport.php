<?php

namespace WBT\PluginLaravel\Models;

use WebTranslator\Collection;
use WebTranslator\Translation;

class AbstractionExport extends AbstractionBase
{
    public function export()
    {
        $collection = new Collection();

        foreach ($this->getDataFromFile() as $group => $abstractNames) {
            foreach ((array_dot($abstractNames)) as $abstractName => $originalValue) {
                $translation = new Translation();
                $translation->addGroup($group);
                $translation->setAbstractName($abstractName);
                $translation->setOriginalValue($originalValue);

                $collection->add($translation);
            }
        }

        return $collection;
    }


    public function getDataFromFile(): array
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

    private function getGroup(string $path): string
    {
        return str_replace([DIRECTORY_SEPARATOR, '.php'], ['::', ''], $path);
    }
}
