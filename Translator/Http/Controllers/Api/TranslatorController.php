<?php

namespace App\Translator\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception;

use Log;

class TranslatorController extends BaseController
{
    //const API_URL = 'http://fnukraine.pp.ua/api/v2/';
    const API_URL = 'http://192.168.88.149:8080/api/v2/';

    protected $localePath = '';
    protected $unprocessedLocales = [];
    protected $processedLocales   = [];
    protected $files              = [];

    protected $client;
    protected $apiKey;
    protected $baseLangDir;
    protected $baseLang;
    protected $languages;

	const REQUEST_SIZE = 100;
	const RECEIVE_SIZE = 1000;

	protected function client()
    {
        if (null === $this->client) {
            $this->client = new Client([
                'base_uri' => self::API_URL
            ]);
        }
        return $this->client;
    }

    protected function getApiKey()
    {
        if (null === $this->apiKey) {
            $this->apiKey = env('TRANSLATOR_API_KEY');
        }
        return $this->apiKey;
    }

    protected function getBaseLangDir()
    {
        if (null === $this->baseLangDir) {
            $this->baseLangDir = base_path() . '/resources/lang/' . $this->baseLang . '/';
        }
        return $this->baseLangDir;
    }
    
    public function init()
    {
        if(!$this->getApiKey()) {
            return response()->json([
                'status' => 'error',
                'message' => 'TRANSLATOR_API_KEY not exists'
            ], 400);
        }

        try {
            $response = $this->client()->get('project?api_key=' . $this->getApiKey());

            if ($response->getBody()) {
                $response = json_decode($response->getBody());

                if (!empty($response->data)) {
                    $this->baseLang = $response->data->language;
                    $this->languages = $response->data->languages;
                }
            }
        } catch(Exception\ConnectException $e) {
            Log::error('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);

        } catch(Exception\ClientException $e) {
            Log::error('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

/*	protected function initTask()
	{
		$this->basePath = base_path();
		if( ! $this->apiKey = env('TRANSLATOR_API_KEY')) {
			\Log::error('TRANSLATOR TRANSLATOR_API_KEY not exists');
			die('TRANSLATOR_API_KEY not exists');
		}

		$this->client = new Client([
			'base_uri' => 'http://fnukraine.pp.ua/'
		]);
		
		try {
			$response = $this->client->get('/api/v2/project?api_key=' . $this->apiKey);
			$response = json_decode($response->getBody());
			$this->baseLang = $response->data->language->code;
			$this->baseLangDir = $this->basePath . '/resources/lang/' . $this->baseLang . '/';
		} catch(\GuzzleHttp\Exception\ConnectException $e) {
			\Log::error('TRANSLATOR ' . $e->getResponse()->getBody()->getContents());
			die($e->getResponse()->getBody()->getContents());
		} catch(\GuzzleHttp\Exception\ClientException $e) {
			\Log::error('TRANSLATOR ' . $e->getResponse()->getBody()->getContents());
			die($e->getResponse()->getBody()->getContents());
		}
	}*/

	/*public function export()
	{
		$this->initTask();

		$this->loadLocales();
		$this->prepareLocales($this->unprocessedLocales);

		if(empty($this->processedLocales))
			return response()->json(['status' => 'success'], 200);

		$this->processedLocales = array_chunk($this->processedLocales, self::REQUEST_SIZE, true);
		$locales = [];

		foreach($this->processedLocales as &$arrLocales) {
			$pack = [];
			foreach($arrLocales as $k => $locale) {
				$pack[] = [
					'name' => $k,
					'value' => $locale
				];
			}
			$locales[]['data'] = $pack;
		}

		foreach($locales as &$locales) {
			try {
				$result = $this->client->post('/api/v2/project/tasks/create?api_key=' . $this->apiKey, [
					'form_params' => $locales
				]);

				$result = json_decode($result->getBody());
			} catch(\GuzzleHttp\Exception\ConnectException $e) {
				\Log::error('TRANSLATOR ' . $e->getResponse()->getBody()->getContents());
			} catch(\GuzzleHttp\Exception\ClientException $e) {
				\Log::warning('TRANSLATOR ' . $e->getResponse()->getBody()->getContents());
			}
		}
		return response()->json(['status' => 'success'], 200);
	}*/

	/*public function import()
	{
		$this->initTask();

		try {
			$projectResponse = json_decode(($this->client->get('api/v2/project?api_key=' . $this->apiKey))->getBody());
			if( ! $projectResponse->data->languages)
				return response()->json(['status' => false, 'message' => 'Languages not found!', 'code' => 404], 404);

			do {
				if(current($projectResponse->data->languages)->code === $this->baseLang)
					continue;

				$response = json_decode($this->client->get('/api/v2/project/translations/' . current($projectResponse->data->languages)->id . '?limit=' . self::RECEIVE_SIZE . '&api_key=' . $this->apiKey)->getBody())->data->data;

				if( ! $response = array_filter($response, function($v) {
					return ! empty($v->translation);
				}))
					continue;

				do {
					current($response)->name = str_replace('/' . $this->baseLang . '::', '/' . current($projectResponse->data->languages)->code . '::', current($response)->name);
					$data = explode('::', current($response)->name);
					
					$this->saveTranslate($data, current($response)->translation->value);
				} while(next($response));
			} while(next($projectResponse->data->languages));
		} catch(\GuzzleHttp\Exception\ConnectException $e) {
			if( ! $e->getCode())
				\Log::error('TRANSLATOR Connection error');
			\Log::error('TRANSLATOR ' . $e->getResponse()->getBody()->getContents());
		} catch(\GuzzleHttp\Exception\ClientException $e) {
			\Log::warning('TRANSLATOR ' . $e->getResponse()->getBody()->getContents());
		}

		return response()->json(['message' => 'success']);
	}*/
}

