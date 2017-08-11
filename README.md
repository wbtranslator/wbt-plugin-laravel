# WBTranslator Plugin Laravel
### Installation by composer
```
composer require wbtranslator/wbt-plugin-laravel
```
### Add TranslatorServiceProvider to PROJECT_PATH/config/app.php in section 'providers'
```
WBTranslator\PluginLaravel\Providers\WBTranslatorServiceProvider::class 
```
### Publish the config using the following command
```
php artisan vendor:publish --provider="WBTranslator\PluginLaravel\Providers\WBTranslatorServiceProvider"
```
### Set folders permissions with write access from server.
```sh
$ chmod -R 775 ./resources/lang/
```
### Set API KEY
Put API KEY to .env file

Example: WBT_API_KEY=API_KEY

### Send abstractions to WBTranslator
```	
php artisan wbt:abstractions:export 
```
### Get abstractions from WBTranslator and save them to lang directory
```	
php artisan wbt:abstractions:import
```
