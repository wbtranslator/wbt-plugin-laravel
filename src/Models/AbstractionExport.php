<?php

namespace WBT\PluginLaravel\Models;

class AbstractionExport extends AbstractionBase
{
    public function getAbstractions(): array
    {
        $abstractions = [];

        foreach ($this->filesystem->allFiles($this->localeDirectory) as $file) {
            $relativePath = $file->getRelativePathname();
            $absolutePath = $this->localeDirectory . $relativePath;
            $data = $this->filesystem->getRequire($absolutePath);

            if (file_exists($absolutePath)) {
                if (!empty($data) && is_array($data)) {
                    $abstractions[$this->alterPathName($relativePath)] = $data;
                }
            }
        }

        return $abstractions;
    }

    private function alterPathName(string $path): string
    {
        return str_replace([DIRECTORY_SEPARATOR, '.php'], ['::', ''], $path);
    }
}
