<?php

namespace App\Translator\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception;
use App\Translator\Models\Translator;
use Log;

class TranslatorController extends BaseController
{
    //const API_URL = 'http://fnukraine.pp.ua/api/v2/';
    const API_URL = 'http://192.168.88.149:8080/api/v2/';

    protected $localePath = '';
    protected $unprocessedLocales = [];
    protected $processedLocales   = [];
    protected $files              = [];

    protected $model;
    protected $client;
    protected $apiKey;

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

    protected function responseError($messsage = null, $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $messsage,
        ], $code);
    }

    protected function responseSuccess($messsage = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $messsage,
        ], $code);
    }

    public function init()
    {
        if(!$this->getApiKey()) {
            return $this->responseError('TRANSLATOR_API_KEY not exists');
        }

        $this->model = new Translator();

        try {
            $response = $this->client()->get('project?api_key=' . $this->getApiKey());

            if ($response->getBody()) {
                $response = json_decode($response->getBody());

                if (!empty($response->data)) {
                    $this->model->setBaseLang($response->data->language);
                    $this->model->setLanguages($response->data->languages);
                }

                return $this->responseSuccess([
                    'base_lang' => $this->model->getBaseLang(),
                    'languages' => $this->model->getLanguages(),
                ]);
            }
        } catch(Exception\ConnectException $e) {
            Log::error('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents());
            return $this->responseError($e->getMessage());

        } catch(Exception\ClientException $e) {
            Log::error('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents());
            return $this->responseError($e->getMessage());
        }
    }

	public function export()
	{
        $this->init();

        $processedLocales = $this->model->locales();

		if (empty($processedLocales)) {
            return $this->responseSuccess();
        }

        $processedLocales = array_chunk($processedLocales, self::REQUEST_SIZE, true);
		$locales = [];

		foreach($processedLocales as &$arrLocales) {
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
				$result = $this->client()->post('project/tasks/create?api_key=' . $this->getApiKey(), [
					'form_params' => $locales
				]);

				if ($result->getBody()) {
                    $result = json_decode($result->getBody());
                    return $this->responseSuccess($result->message);
                }
			} catch(Exception\ConnectException $e) {
				Log::error('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents());
                return $this->responseError($e->getMessage());
			} catch(Exception\ClientException $e) {
				Log::warning('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents());
                return $this->responseError($e->getMessage());
			}
		}
	}

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
					current($response)->name = preg_replace('/\/(' . $this->baseLang . ')/', '/' . current($projectResponse->data->languages)->code, current($response)->name);
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

