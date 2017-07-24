<?php

namespace WBT\LaravelPlugin\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use WBT\LaravelPlugin\Models\Translator;
use Exception;
use Log;

class ApiController extends BaseController
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
}

