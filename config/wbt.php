<?php

return [
    'api_key' => env('WBT_API_KEY'),
    
    'locale' => 'en',
    
    'lang_paths' => [
        '/resources/lang',
        '/vendor/my_plugin/lang',
        '/storage/lang/',
    ],
    
    'format' => 'array',
    
    'group_delimiter' => '::',
];