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
     * @return void
     */
    public function handle()
    {
        $debug = $this->option('debug');

        $this->startInfo($debug);

        $abstractions = $this->model->export();

        if (!$abstractions->isEmpty() && $debug) {
            $abstractions = $abstractions->map(function (array $item){
                $item['name'] = str_limit($item['name'], 20);
                $item['value'] = str_limit($item['value'], 20);

                return $item;
            })->toArray();

            $this->table(['Name', 'Value', 'CountWords', 'GroupId', 'Id'], $abstractions);

        } else {
            $this->info('Data is empty. Nothing sent to WBT');
        }

        $this->endInfo($abstractions);
        $this->warning();
    }
}
