<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

class AbstractionsExportCommand extends AbstractionsBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbt:abstractions:export {--debug}';

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
        $debug = $this->option('debug');

        $this->startInfo($debug);

        $data = $this->model->export();
        $result = $data['collection'];

        !empty($result) && $debug ? $this->table(['Name', 'Value', 'CountWords', 'GroupId', 'Id'], $result) :
            $this->info('Data is empty. Nothing sent to WBT');

        $this->endInfo($result);
        $this->warning($data['warnings']);
    }
}
