# WBTranslator Plugin Laravel
### Installation by composer
```console
$ composer require wbtranslator/wbt-plugin-laravel dev-master
```
### Add TranslatorServiceProvider to PROJECT_PATH/config/app.php in section 'providers'
```php
'providers' => [
    // ...
    WBTranslator\PluginLaravel\Providers\WBTranslatorServiceProvider::class
] 
```
### Publish the config using the following command
```console
$ php artisan vendor:publish --provider="WBTranslator\PluginLaravel\Providers\WBTranslatorServiceProvider"
```
### Set folders permissions with write access from server.
```console
$ chmod -R 775 ./resources/lang/
```
### Set API KEY
Put API KEY to .env file

Example: WBT_API_KEY=API_KEY

### Send abstractions to WBTranslator
```console	
$ php artisan wbt:abstractions:export 
```
### Get abstractions from WBTranslator and save them to lang directory
```console	
$ php artisan wbt:abstractions:import
```
