<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

use WBTranslator\Sdk\Translation;

class AbstractionsImportCommand extends AbstractionsBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbt:abstractions:import {--debug}';

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
        $debug = $this->option('debug');

        $this->startInfo($debug);

        $result = $this->model->import()->map(function (Translation $item) {
            $item->removeGroup();
            return (array)$item;
        });

        !empty($result) && $debug ? $this->table(['OriginalName', 'Value', 'Language', 'Translation', 'Comment'], $result->toArray()) :
            $this->info('Data is empty. Nothing get from WBT');

        $this->endInfo($result);
    }
}
