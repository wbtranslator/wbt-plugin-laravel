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




Копируем плагин в ./app/
Добавляем строку "App\\Translator\\": "app/translator" в psr-4 в composer.json
В config/app.php providers => App\Translator\Providers\TranslatorServiceProvider::class
php artisan cache:clear