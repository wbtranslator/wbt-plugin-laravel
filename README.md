# WEB Translator Laravel Plugin

### Download plugin
https://bitbucket.org/futurenetukraine/fwt-plugin-laravel/get/74605205c8b7.zip

### Copy plugin folder Translator to PROJECT_PATH/app 
```
$ cp Translator PROJECT_PATH/app/
```

### Set folders permissions with write access from server.
```sh
$ chmod -R 775 ./resources/lang/
```

## Add TranslatorServiceProvider to PROJECT_PATH/config/app.php in section 'providers'
```
App\Translator\Providers\TranslatorServiceProvider::class 
```
## Get API KEY
Put API KEY to .env file

Example: TRANSLATOR_API_KEY=API_KEY

## Use web interfase
```	
http://PROJECT_URL/translator
```

## API methods
```	
http://PROJECT_URL/translator/api/export
```
```
http://PROJECT_URL/translator/api/import
```