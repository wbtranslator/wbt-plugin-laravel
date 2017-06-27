# Deploy translator plugin to laravel root

### Set folders permissions with write access from server.
```sh
$ chmod -R 775 ./resources/lang/
```

# Get API KEY
## Put API KEY to .env file
### Example: TRANSLATOR_API_KEY=API_KEY

## Add TranslatorServiceProvider to YOUR_PROJECT/config/app.php in section 'providers'
```
App\Translator\Providers\TranslatorServiceProvider::class 
```

# API
```	
/translator/api/export
```
```
/translator/api/import
```