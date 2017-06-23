<?php

namespace Translator\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class TranslatorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app['router']->group([
			'prefix' => '/translator/api', 
			'namespace' => 'Translator\Http\Controllers\Api'], 
		function($router) {
			$router->get('/v1/get/api', 'TranslatorController@getApi');
			$router->get('/v1/translate/request', 'TranslatorController@requestTranslate');
			$router->get('/v1/translate/receive', 'TranslatorController@receiveTranslate');
		});
	}

	public function register(){}
}