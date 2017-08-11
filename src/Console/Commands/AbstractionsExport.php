<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

use Illuminate\Console\Command;
use WBTranslator\PluginLaravel\Models\WBTranslatorAbstractionsModel;

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
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Process ... ');
    
        $model = new WBTranslatorAbstractionsModel;
        $result = $model->export();
        
        $this->info('Send ' . !empty($result) ? count($result) : 0 . ' abstractions to WBTranslator');
    }
}
