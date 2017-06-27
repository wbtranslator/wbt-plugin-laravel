<?php

namespace Translator\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;

class TranslatorController extends BaseController
{
	private $localePath = '';
	private $unprocessedLocales = [];
	private $processedLocales   = [];
	private $files              = [];

	private $client;
	private $apiKey;
	private $baseLangDir;
	private $baseLang;
	private $basePath;

	const REQUEST_SIZE = 100;
	const RECEIVE_SIZE = 1000;

	public function initTask()
	{
		$this->basePath = base_path();
		$this->apiKey = env('TRANSLATOR_API_KEY');
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
	}

	/**
	 * @method GET
	 * @url /translator/api/v1/translate/request
	 */
	public function requestTranslate()
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
	}

	/**
	 * @method GET
	 * @url /translator/api/v1/translate/receive
	 */
	public function receiveTranslate()
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
	}

	protected function saveTranslate(Array &$data, &$translate)
	{
		$path = $data[1];
		$file = $data[2];

		unset($data[0], $data[1], $data[2]);

		$data = array_values($data);

		$path = $this->createTranslatePath($path);
		$this->createTranslateFile($file, $path, $data, $translate);
	}

	protected function makeArray(Array &$array) {
		if(count($array) > 1)
			return [array_shift($array) => $this->makeArray($array)];
		else
			return $array[0];
	}

	protected function createTranslateFile($fileName, $path, $array, $translate)
	{
		array_push($array, $translate);
		if(file_exists($path . '/' . $fileName)) {
			if( ! isset($this->files[$path . '/' . $fileName]))
				$this->files[$path . '/' . $fileName] = require_once $path . '/' . $fileName;

			$data = array_replace_recursive($this->files[$path . '/' . $fileName], $this->makeArray($array));
		} else
			$data = $this->makeArray($array);

		$this->files[$path . '/' . $fileName] = $data;

		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		$data = str_replace("{\n", "[\n", 
			str_replace("}\n", "]\n", 
				str_replace("},\n", "],\n",
					str_replace('":', '" =>', $data))));
		$data[strlen($data) - 1] = "]";
		$data = trim(str_replace('    ', "\t", $data));
		file_put_contents($path . '/' . $fileName, "<?php\n\nreturn " . $data . ";");
	}

	private function createTranslatePath($unprocessedPath) 
	{
		$unprocessedPath = explode('/', $unprocessedPath);
		$path = $this->basePath . '/resources';

		do {
			$path.=  '/' . current($unprocessedPath);
			if( ! is_dir($path))
				mkdir($path);
		} while(next($unprocessedPath));

		return $path;
	}

	protected function loadLocales()
	{
		if( ! is_dir($this->baseLangDir))
			return false;

		if( ! $files = scandir($this->baseLangDir))
			return null;

		$_path = substr($this->baseLangDir, strpos($this->baseLangDir, 'lang'), -1);

		do {
			if(current($files) === '.' || current($files) === '..')
				continue;

			if(is_dir($this->baseLangDir . current($files)))
				$this->loadLocales($path . current($files) . '/');
			else
				$this->unprocessedLocales[$_path][current($files)] = require_once $this->baseLangDir . current($files);
		} while(next($files));
	}

	protected function prepareLocales($unprocessedLocales, $path = '')
	{
		if(empty($unprocessedLocales))
			return false;

		reset($unprocessedLocales);
		do {
			if(is_array(current($unprocessedLocales)))
				$this->prepareLocales(current($unprocessedLocales), $path . '::' . key($unprocessedLocales));
			else
				$this->processedLocales[$path . '::' . key($unprocessedLocales)] = current($unprocessedLocales);
		} while(next($unprocessedLocales));
	}
}
