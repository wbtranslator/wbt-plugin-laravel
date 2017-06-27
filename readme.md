# Deploy translator plugin to laravel root

### Set folders permissions with write access from server.
```sh
$ chmod -R 775 ./resources/lang/
```

### Copy plugin folder to YOUR_PROJECT/app 

## Add TranslatorServiceProvider to YOUR_PROJECT/config/app.php in section 'providers'
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