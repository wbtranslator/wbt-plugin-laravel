<?php

namespace App\Translator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Translator\Models\Translator;

class TranslatorController extends BaseController
{
    public function __construct()
    {
        view()->addLocation(app_path() . '/Translator/resources/views');
    }

    public function index(Request $request)
	{
		$viewVars = [
			'exported' => null,
			'imported' => null,
		];

		try {
            $translator = new Translator(env('TRANSLATOR_API_KEY'));

            switch ($request->input('action')) {
                case 'import':
                    $viewVars['imported'] = $translator->import();
                    break;

                case 'export':
                    $viewVars['exported'] = $translator->export();
                    break;
            }
        }
        catch(\Exception $e) {
            return view('translator', ['exception' => $e]);
        }

        return view('translator', $viewVars);
	}
}