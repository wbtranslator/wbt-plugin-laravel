<?php

namespace App\Translator\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class TranslatorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app['router']->group([
			'prefix' => '/translator/api', 
			'namespace' => 'App\Translator\Http\Controllers\Api'], 
		function($router) {
            #$router->get('/init', 'TranslatorController@init');
			$router->get('/export', 'TranslatorController@export');
			$router->get('/import', 'TranslatorController@import');
		});

		$this->app['router']->group([
			'prefix' => '/translator', 
			'namespace' => 'App\Translator\Http\Controllers'], 
		function($router) {
			View::addLocation(app_path() . '/Translator/resources/views');
			$router->get('/', 'TranslatorController@init');
		});
	}

	public function register() {}
}

