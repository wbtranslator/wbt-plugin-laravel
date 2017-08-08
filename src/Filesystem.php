<?php

namespace WBTranslator\PluginLaravel;

class Filesystem
{
    public function getAllFiles($path)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        $arr = [];

        while ($iterator->valid()) {
            if (!$iterator->isDot()) {
                $arr[] = [
                    'basePathname' => dirname($path, 1),
                    'relativePathname' => $iterator->getSubPathName(),
                    'absolutePathname' => $iterator->key(),
                ];
            }

            $iterator->next();
        }

        return $arr;
    }

    public function getRequire($path)
    {
        if (is_file($path)) {
            return require $path;
        }

        throw new \Exception("File does not exist at path {$path}");
    }
}
