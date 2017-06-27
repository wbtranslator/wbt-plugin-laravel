<?php

namespace App\Translator\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use URL;
use GuzzleHttp\{
	Client,
	Exception
};

class TranslatorController extends BaseController
{
	protected $client;

	protected function client()
    {
        if (null === $this->client) {
			$url = URL::to('/translator/api') . '/';
            $this->client = new Client([
                'base_uri' => $url
            ]);
        }
        return $this->client;
    }

	public function init(Request $request)
	{
		$viewVars = [
			'exported' => [],
			'imporded' => []
		];

		$action = $request->input('action');
		switch($action) {
			case 'import':
				$response = $this->client()->get('import');
				$viewVars['imported'] = json_decode($response->getBody(), TRUE);
				break;

			case 'export':
				$response = $this->client()->get('export');
				$viewVars['exported'] = json_decode($response->getBody(), TRUE);
				break;
		}

		return view('translator', $viewVars);
	}
}
