<?php

namespace WBTranslator\PluginLaravel\Models;

use WBTranslator\PluginLaravel\Filesystem;

/**
 * Class AbstractionBase
 *
 * @package WBTranslator\PluginLaravel
 */
abstract class AbstractionBase
{
    const DEFAULT_GROUP_DELIMITER = '::';
    
    /**
     * @var array
     */
    protected $langPaths = [];
    
    protected $basePath;
    
    /**
     * @var string
     */
    protected $locale;
    
    protected $config;
    
    /**
     * @var string
     */
    protected $groupDelimiter;
    
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
        $this->config = config('wbt');
        
        $this->locale = $config['locale'] ?? app()->getLocale();
    
        $this->basePath = app()->basePath();
        
        $this->langPaths = $this->langPaths();
    
        $this->groupDelimiter = $this->config['group_delimiter'] ?? self::DEFAULT_GROUP_DELIMITER;
    
        $this->filesystem = new Filesystem;
        $this->localeDirectories = $this->localeDirectories();
    }
    
    public function langPaths(): array
    {
        //array_push($this->langPaths, app()->langPath());
        
        if (!empty($this->config['lang_paths'])) {
            $this->langPaths = array_merge($this->langPaths, $this->config['lang_paths']);
        
            $this->langPaths = array_map(function($el) {
                return rtrim($el, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }, $this->langPaths);
        }
        
        return $this->langPaths;
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
