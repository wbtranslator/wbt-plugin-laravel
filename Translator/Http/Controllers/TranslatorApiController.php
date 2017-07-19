<?php

namespace App\Translator\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Translator\Models\Translator;
use Exception;
use Log;

class TranslatorApiController extends BaseController
{
    protected $model;

    const REQUEST_SIZE = 100;
    const RECEIVE_SIZE = 1000;

    public function __construct()
    {
        $this->model = new Translator();
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
        try {
        } catch (Exception $e) {
            Log::error('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents() . '; Exception: ' . $e->getMessage());
            return $this->responseError($e->getMessage());
        }
    }

    public function export()
    {
        try {
            $this->model->export();
        } catch (Exception $e) {
            Log::error('TRANSLATOR: ' . $e->getMessage());
            return $this->responseError($e->getMessage());
        }

        return $this->responseSuccess();
    }

    public function import()
    {
        try {
            $this->model->import();
        } catch (Exception $e) {
            Log::error('TRANSLATOR: ' . $e->getMessage());
            return $this->responseError($e->getMessage());
        }

        return $this->responseSuccess();
    }

//    public function import()
//    {
//        $this->init();
//
//        $baseLang = $this->model->getBaseLang();
//        $languages = $this->model->getLanguages();
//
//        $result =[];
//
//        try {
//            foreach ($languages as $language) {
//                if($language->id === $baseLang->id) {
//                    continue;
//                }
//
//                $url = 'project/translations/' . $language->id . '?limit=' . self::RECEIVE_SIZE . '&api_key=' . $this->getApiKey();
//                $response = $this->client()->get($url);
//                $body = $response->getBody();
//
//                if ($body) {
//                    $body = json_decode($body);
//
//                    if (!empty($body->data->data)) {
//                        $data = $body->data->data;
//
//                        if (!$data = array_filter($data, function($v) {
//                            return !empty($v->translation);
//                        })) {
//                            continue;
//                        }
//
//                        $result[$language->code] = 0;
//
//                        foreach ($data as $d) {
//                            $d->name = preg_replace('/\/(' . $baseLang->code . ')/', '/' . $language->code, $d->name);
//                            $this->model->saveTranslate($d->name, $d->translation->value);
//                            $result[$language->code]++;
//                        }
//                    }
//                }
//            }
//        } catch(Exception $e) {
//            Log::error('TRANSLATOR: ' . $e->getMessage());
//            return $this->responseError($e->getMessage());
//        }
//
//        return $this->responseSuccess($result);
//    }
}

