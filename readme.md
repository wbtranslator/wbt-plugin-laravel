Deploy translator plugin to laravel root
Set chmod 0777 to /lang

Get API KEY
Put API KEY to /.env
	Example: TRANSLATOR_API_KEY=API_KEY

Add 'providers' => Translator\Providers\TranslatorServiceProvider::class to YOUR_PROJECT/config/app.php
Clear cache: artisan cache:clear

API
	/translator/api/v1/translate/request
	/translator/api/v1/translate/receive