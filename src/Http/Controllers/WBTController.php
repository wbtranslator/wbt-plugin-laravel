<?php

namespace WBT\PluginLaravel\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Log;
use WBT\PluginLaravel\Models\AbstractionExport;
use WBT\PluginLaravel\Models\AbstractionImport;
use WebTranslator\WebTranslator;

class WBTController extends BaseController
{
    protected $sdk;

    const REQUEST_SIZE = 100;
    const RECEIVE_SIZE = 1000;

    public function __construct()
    {
        $this->sdk = new WebTranslator(config('wbt.api_key'), $client ?? null);
    }

    public function export()
    {
        $abstractionExport = new AbstractionExport();
        $dataForExport = $abstractionExport->export();
        $this->sdk->translations()->create($dataForExport);

        return $this->responseSuccess();
    }

    public function import()
    {
        $abstractionImport = new AbstractionImport();
        $translations = $this->sdk->translations()->all();
        $abstractionImport->import($translations);

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