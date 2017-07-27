<?php

namespace WBT\PluginLaravel\Models;

use Illuminate\Filesystem\Filesystem;

abstract class AbstractionBase
{
    protected $langPath;
    protected $locale;
    protected $filesystem;
    protected $localeDirectory;

    public function __construct(string $locale = null)
    {
        $this->locale = app()->getLocale();
        $this->langPath = app()->langPath();
        $this->filesystem = new Filesystem;
        $this->localeDirectory = $this->getLocaleDirectory($locale);
    }

    protected function getLocaleDirectory(string $locale = null): string
    {
        return rtrim($this->langPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .
            ($locale ?: $this->locale) . DIRECTORY_SEPARATOR;
    }
}
