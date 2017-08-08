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

        foreach ($this->langPaths() as $localeDirectory) {
            if (!file_exists($basePath = $this->getLocalePath($localeDirectory))) {
                continue;
            }

            $rootGroup = $this->createGroup($localeDirectory);

            foreach ($this->filesystem->getAllFiles($basePath) as $file) {

                $data = $this->filesystem->getRequire($file['absolutePathname']);

                if (file_exists($file['absolutePathname'])) {

                    if (!empty($data) && is_array($data)) {
                        $group = $this->createGroup($file['relativePathname'], $rootGroup);

                        foreach ((self::arrayDot($data)) as $abstractName => $originalValue) {
                            if (!$abstractName) {
                                continue;
                            }

                            $translation = $this->createTranslation($abstractName, $originalValue, $group);
                            $collection->add($translation);
                        }
                    }
                }
            }
        }

        return $collection;
    }

    protected function createTranslation($abstractName, $originalValue, $group)
    {
        $translation = new WBTranslatorSdk\Translation;

        $translation->setAbstractName($abstractName);
        $translation->setOriginalValue(!empty($originalValue) ? (string)$originalValue : '');
        $translation->addGroup($group);

        return $translation;
    }

    protected function createGroup(string $path, $parent = null)
    {
        $path = trim($path, DIRECTORY_SEPARATOR);
        $name = str_replace([DIRECTORY_SEPARATOR, '.php'], [$this->groupDelimiter, ''], $path);

        $group = new WBTranslatorSdk\Group();
        $group->setName($name);

        if (null !== $parent) {
            $group->addParent($parent);
        }

        return $group;
    }

    protected function getLocalePath($localeDirectory)
    {
        return $this->basePath . $localeDirectory . $this->locale . DIRECTORY_SEPARATOR;
    }

}
