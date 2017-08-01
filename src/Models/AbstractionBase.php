<?php

namespace WBTranslator\PluginLaravel\Models;

use Illuminate\Filesystem\Filesystem;

/**
 * Class AbstractionBase
 *
 * @package WBTranslator\PluginLaravel
 */
abstract class AbstractionBase
{
    const GROUP_DELIMITER = '::';
    
    /**
     * @var
     */
    protected $langPath;
    
    /**
     * @var
     */
    protected $locale;
    
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var string
     */
    protected $localeDirectory;
    
    protected $sdk;
    
    /**
     * AbstractionBase constructor.
     *
     * @param string|null $locale
     */
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
    
    public static function arrayDot($array)
    {
        return \Arr::dot($array);
    }
    
    public static function arraySet(&$array, $key, $value)
    {
        return \Arr::set($array, $key, $value);
    }
    
    public static function arrayLast($array)
    {
        return \Arr::last($array);
    }
}
