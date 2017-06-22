<?php

namespace Translator\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;

class TranslatorController extends BaseController
{
	private $localePath = '';
	private $unprocessedLocales = [];
	private $processedLocales   = [];

	public function index()
	{
		if( ! $key = env('TRANSLATOR_API_KEY'))
			return response()->json(['status' => false, 'message' => 'Token is absent!', 'code' => 401], 401);

		try {
			$client = new Client([
				'base_uri' => 'http://192.168.88.149:8080/'
			]);

			$response = json_decode(($client->get('api/v2/project?api_key=' . $key))->getBody());
			return response()->json(['status' => true, 'data' => [
				'language' => $response->data->language,
				'languages' => $response->data->languages
			]], 200);
		} catch(\GuzzleHttp\Exception\ConnectException $e) {
			return response()->json(json_decode($e->getResponse()->getBody()->getContents()), 500);
		}
	}

	public function requestTranslate()
	{
		if( ! $key = env('TRANSLATOR_API_KEY'))
			return response()->json(['status' => false, 'message' => 'Token is absent!', 'code' => 401], 401);

		$this->localePath = base_path() . '/resources/lang/';
		$this->loadLocales($this->localePath);
		$this->prepareLocales($this->unprocessedLocales);

		if(empty($this->processedLocales))
			return response()->json(['status' => 'success'], 200);

		$client = new Client([
			'base_uri' => 'http://192.168.88.149:8080/'
		]);

		reset($this->processedLocales);
		do {
			try {
				$client->post('/api/v2/project/tasks/create?api_key=' . $key, [
					'form_params' => [
						'name' => key($this->processedLocales),
						'value' => current($this->processedLocales)
					]
				]);
			} catch(\GuzzleHttp\Exception\ConnectException $e) {
				// return response()->json(json_decode($e->getResponse()->getBody()->getContents()), 500);
			} catch(\GuzzleHttp\Exception\ClientException $e) {
				// return response()->json(json_decode($e->getResponse()->getBody()->getContents()), 500);
			}
		} while(next($this->processedLocales));

		return response()->json(['status' => 'success'], 200);
	}

	public function receiveTranslate()
	{
		if( ! $key = env('TRANSLATOR_API_KEY'))
			return response()->json(['status' => false, 'message' => 'Token is absent!', 'code' => 401], 401);

		try {
			$client = new Client([
				'base_uri' => 'http://192.168.88.149:8080/'
			]);

			// ToDo: Get all languages
			$projectResponse = json_decode(($client->get('api/v2/project?api_key=' . $key))->getBody());
			if( ! $projectResponse->data->languages)
				return response()->json(['status' => false, 'message' => 'Languages not found!', 'code' => 404], 404);

			do {
				current($projectResponse->data->languages)->code;
				$response = json_decode($client->get('/api/v2/project/translations/' . current($projectResponse->data->languages)->id . '/?api_key=' . $key)
					->getBody())->data->data;
				do {
					if('::' === substr(current($response)->name, 0, 2)) {
						current($response)->name = str_replace('/' . \Config::get('app.locale') . '::', '/' . current($projectResponse->data->languages)->code . '::', current($response)->name);
						$data = explode('::', current($response)->name);
						$translate = isset(current($response)->translation->value) ? current($response)->translation->value : '';

						dump($data);
						dump($translate);
						if(strlen($translate))
							$this->saveTranslate($data, $translate);
					}
				} while(next($response));
			} while(next($projectResponse->data->languages));
		} catch(\GuzzleHttp\Exception\ConnectException $e) {
			if( ! $e->getCode())
				return response()->json(['message' => 'Connection error'], 500);
			return response()->json(json_decode($e->getResponse()->getBody()->getContents()), 500);
		} catch(\GuzzleHttp\Exception\ClientException $e) {
			return response()->json(json_decode($e->getResponse()->getBody()->getContents()), 500);
		}
	}

	private function saveTranslate(Array &$data, &$translate)
	{
		$path = $data[1];
		$file = $data[2];

		unset($data[0], $data[1], $data[2]);

		$data = array_values($data);

		$path = $this->createTranslatePath($path);
		$this->createTranslateFile($file, $path, $data, $translate);
	}

	private function makeArray(&$array) {
		if(count($array) > 1)
			return [array_shift($array) => $this->makeArray($array)];
		else
			return $array[0];
	}

	private function createTranslateFile($fileName, $path, $array, $translate)
	{
		array_push($array, $translate);

		if(file_exists($path . '/' . $fileName)) {
			$data = require_once $path . '/' . $fileName;
			if( ! is_array($data))
				$data = [];

			$data = array_replace_recursive($data, $this->makeArray($array));
		} else
			$data = $this->makeArray($array);

		$data = json_encode($data, JSON_PRETTY_PRINT);
		$data = str_replace("{\n", "[\n", 
			str_replace("}\n", "]\n", 
				str_replace("},\n", "],\n",
					str_replace('":', '" =>', $data))));
		$data[strlen($data) - 1] = "]";
		$data = str_replace('    ', '	', $data);

		file_put_contents($path . '/' . $fileName, "<?php\n\nreturn " . $data . ";");
	}

	private function createTranslatePath($unprocessedPath) 
	{
		$unprocessedPath = explode('/', $unprocessedPath);
		$path = base_path() . '/resources';

		do {
			$path.=  '/' . current($unprocessedPath);
			if( ! is_dir($path))
				mkdir($path);
		} while(next($unprocessedPath));

		return $path;
	}

	private function loadLocales($path)
	{
		if( ! is_dir($path))
			return false;
		
		if( ! $files = scandir($path))
			return null;

		$_path = substr($path, strpos($path, 'lang'), -1);
		do {
			if(current($files) === '.' || current($files) === '..')
				continue;

			if(is_dir($path . current($files))) {
				if(current($files) !== \Config::get('app.locale'))
					continue;
				$this->loadLocales($path . current($files) . '/');
			} else
				$this->unprocessedLocales[$_path][current($files)] = require_once $path . current($files);
		} while(next($files));
	}

	private function prepareLocales($unprocessedLocales, $path = '')
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