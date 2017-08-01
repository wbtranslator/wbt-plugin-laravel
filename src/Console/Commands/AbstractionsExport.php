<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

use Illuminate\Console\Command;
use WBTranslator\PluginLaravel\Http\Controllers\WBTranslatorController;
use WBTranslator\PluginLaravel\Models\AbstractionExport;

class AbstractionsExport extends  Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abstractions:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $export;
    protected $model;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->export = new WBTranslatorController();
        $this->model = new AbstractionExport();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $abstractions = count($this->model->abstractions());

        $this->info('Find ' . $abstractions . ' abstract names' . PHP_EOL . 'Process ... ');

        $bar = $this->output->createProgressBar($abstractions);
        $response = $this->export->export();

        $bar->finish();

        if ($response->getStatusCode() === 200) {
            $this->info(PHP_EOL . $response->getContent());
        } else {
            $this->info(PHP_EOL . 'Something went wrong');
        }
    }

}