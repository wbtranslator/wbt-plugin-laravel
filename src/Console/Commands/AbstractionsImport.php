<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

use Illuminate\Console\Command;
use WBTranslator\PluginLaravel\Http\Controllers\WBTranslatorController;

class AbstractionsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbt:abstractions:import';

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
        $response = $this->controller->import();
        $this->info($response);
    }
}
