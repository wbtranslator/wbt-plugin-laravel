<?php

namespace App\Translator\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Translator\Models\Translator;
use Exception;
use Log;

class TranslatorApiController extends BaseController
{
    protected $translator;

    public function __construct()
    {
        $this->translator = new Translator(env('TRANSLATOR_API_KEY'));
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
            $data = $this->translator->init();
            return $this->responseSuccess($data);
        } catch(Exception $e) {
            Log::error('TRANSLATOR: ' . $e->getResponse()->getBody()->getContents() . '; Exception: ' . $e->getMessage());
            return $this->responseError($e->getMessage());
        }
    }

	public function export()
	{
        try {
	        $result = $this->translator->export();
            return $this->responseSuccess($result);
        }
        catch(Exception $e) {
            Log::error('TRANSLATOR: ' . $e->getMessage());
            return $this->responseError($e->getMessage());
        }
	}

    public function import()
    {
        try {
            $result = $this->translator->import();
            return $this->responseSuccess($result);
        }
        catch(Exception $e) {
            Log::error('TRANSLATOR: ' . $e->getMessage());
            return $this->responseError($e->getMessage());
        }
    }
}

