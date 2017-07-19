<?php

namespace App\Translator\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TranslatorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::namespace('App\Translator\Http\Controllers')
            ->group(app_path('Translator/routes/translator.php'));
    }
}
