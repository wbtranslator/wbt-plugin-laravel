<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

use Illuminate\Console\Command;
use WBTranslator\PluginLaravel\Models\WBTranslatorAbstractionsModel;

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
    protected $description = 'Get abstractions from WBTranslator and save them to lang directory.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Process ... ');
    
        $model = new WBTranslatorAbstractionsModel;
        $result = $model->import();
        
        $this->info('Get ' . count($result) . ' abstractions from WBTranslator');
    }
}
