<?php

namespace WBTranslator\PluginLaravel\Http\Controllers;

use Log;
use Illuminate\Routing\Controller as BaseController;
use WBTranslator\PluginLaravel\Models\AbstractionExport;
use WBTranslator\PluginLaravel\Models\AbstractionImport;
use WBTranslator\WBTranslatorSdk;

/**
 * Class WBTranslatorController
 *
 * @package WBTranslator\PluginLaravel
 */
class WBTranslatorController extends BaseController
{
    protected $sdk;
    
    public function __construct()
    {
        $this->sdk = new WBTranslatorSdk(config('wbt.api_key'));
    }

    public function export()
    {
        $export = new AbstractionExport();
        $data = $export->abstractions();
    
        try {
            $result = $this->sdk->translations()->create($data);
            return $this->responseSuccess(count($result));
        } catch (\Exception $e) {
            return $this->responseError();
        }
    }

    public function import()
    {
        try {
            $translations = $this->sdk->translations()->all();
        } catch (\Exception $e) {
            return $this->responseError();
        }
        
        $abstractionImport = new AbstractionImport();
        $abstractionImport->saveAbstractions($translations);

        return $this->responseSuccess();
    }

    private function responseError($message = null, $code = 400)
    {
        return response()->json(['status' => 'error', 'message' => $message], $code);
    }

    private function responseSuccess($message = null, $code = 200)
    {
        return response()->json(['status' => 'success', 'message' => $message], $code);
    }
}