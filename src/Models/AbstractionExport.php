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
                if (!$abstractName) {
                    continue;
                }

                $translation = new Translation();
                $translation->setGroup($group);
                $translation->setAbstractName($abstractName);
                $translation->setOriginalValue(!empty($originalValue) ? (string)$originalValue : '');

                $collection->add($translation);
            }
        }

        return $collection;
    }

    private function getDataFromFile(): array
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
