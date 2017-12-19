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
            $item->setAbstractName(str_limit($item->getAbstractName(), 20));
            $item->setOriginalValue(str_limit($item->getOriginalValue(), 20));
            $item->setTranslation(str_limit($item->getTranslation(), 20));
            $item->setComment(str_limit($item->getComment(), 20));
            $item->removeGroup();

            return (array)$item;
        })->toArray();

        !empty($result) && $debug ? $this->table(['OriginalName', 'Value', 'Language', 'Translation', 'Comment'], $result) :
            $this->info('Data is empty. Nothing get from WBT');

        $this->endInfo($result);
    }
}
