# WBTranslator Plugin Laravel
### Installation by composer
```
composer require wbtranslator/wbt-plugin-laravel
```
### Add TranslatorServiceProvider to PROJECT_PATH/config/app.php in section 'providers'
```
WBTranslator\PluginLaravel\Providers\WBTranslatorServiceProvider::class 
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
artisan wbt:abstractions:export 
```
### Get abstractions from WBTranslator and save them to lang directory
```	
artisan wbt:abstractions:import
```
