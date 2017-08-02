<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

use Illuminate\Console\Command;
use WBTranslator\PluginLaravel\Http\Controllers\WBTranslatorController;

class Abstractions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbt:abstractions {type : import or export}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get abstractions from WBTranslator and save them to lang directory';

    protected $controller;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->controller = new WBTranslatorController();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Process ... ');


        $type = $this->argument('type');

        if ($type === 'import') {
            $response = $this->controller->import();
        }

        if ($type === 'export') {
            $response = $this->controller->import();
        }

        $this->info($response ?? 'Please enter correct type');
    }
}