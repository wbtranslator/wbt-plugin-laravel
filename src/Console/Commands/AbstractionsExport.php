<?php
namespace WBTranslator\PluginLaravel\Console\Commands;

use Illuminate\Console\Command;
use WBTranslator\PluginLaravel\Http\Controllers\WBTranslatorController;

class AbstractionsExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbt:abstractions:export';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send abstractions to WBTranslator';
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
        $response = $this->controller->export();
        $this->info($response->getContent());
    }
}
