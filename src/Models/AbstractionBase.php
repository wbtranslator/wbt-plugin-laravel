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
     * @var array
     */
    protected $langPaths = [];
    
    /**
     * @var string
     */
    protected $locale;
    
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var array
     */
    protected $localeDirectories = [];
    
    /**
     * AbstractionBase constructor.
     */
    public function __construct()
    {
        $config = config('wbt');
        
        $this->locale = null !== $config->locale ?? app()->getLocale();
    
        array_push($this->langPaths, app()->langPath());
        if (!empty($config->lang_paths)) {
            $this->langPaths = array_merge($this->langPaths, $config->lang_paths);
        }
        
        $this->filesystem = new Filesystem;
        $this->localeDirectories = $this->localeDirectories();
    }
    
    /**
     * @return array
     */
    public function localeDirectories(): array
    {
        $locale = $this->locale;
        
        return array_map(function($el) use ($locale) {
            return rtrim($el, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR;
        }, $this->langPaths);
    }
    
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array $array
     * @param string $prepend
     * @return array
     */
    public static function arrayDot($array, $prepend = '')
    {
        $results = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::arrayDot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        
        return $results;
    }
    
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function arraySet(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        
        $keys = explode('.', $key);
        
        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            
            $array = &$array[$key];
        }
        
        $array[array_shift($keys)] = $value;
        
        return $array;
    }
    
    /**
     * Return the last element in an array.
     *
     * @param  array  $array
     * @return mixed
     */
    public static function arrayLast($array)
    {
        return empty($array) ? null : end($array);
    }
}
