<?php

/**
 * Translator Routing.
 */

Route::group(['prefix' => 'translator'], function () {
    Route::group(['prefix' => 'api'], function () {
        Route::get('init', 'TranslatorApiController@init');
        Route::get('export', 'TranslatorApiController@export');
        Route::get('import', 'TranslatorApiController@import');
    });

    Route::get('/', 'TranslatorController@index');
});