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
### Get API KEY
Put API KEY to .env file

Example: WBT_API_KEY=API_KEY

### Use web interfase
#### Send abstractions to WBTranslator
```	
http://PROJECT_URL/wbt/export 
```
#### Get abstractions from WBTranslator and save them to lang directory
```	
http://PROJECT_URL/wbt/import
```
