<?php

namespace WBTranslator\PluginLaravel\Console\Commands;

use WBTranslator\PluginLaravel\Models\WBTranslatorAbstractionsModel;
use Illuminate\Console\Command;
use WBTranslator\Sdk\Interfaces\ConfigInterface;
use WBTranslator\Sdk\WBTranslatorSdk;

abstract class AbstractionsBaseCommand extends Command
{
    /** @var WBTranslatorAbstractionsModel  */
    protected $model;

    /** @var WBTranslatorSdk  */
    protected $sdk;

    /** @var ConfigInterface  */
    protected $sdkConfig;


    public function __construct()
    {
        $this->model = new WBTranslatorAbstractionsModel();
        $this->sdk = $this->model->sdk();
        $this->sdkConfig = $this->sdk->config();

        parent::__construct();
    }

    protected function startInfo($debug)
    {
        if ($debug) {
            $this->info(sprintf('Connecting to (%s) ...',
                $this->sdkConfig->getClient()->getConfig('base_uri')->getHost()));
            $this->line(sprintf('Api Key : %s', $this->sdkConfig->getApiKey()));
            $this->line(sprintf('Delimiter : %s', $this->sdkConfig->getDelimiter()));
            $this->line(sprintf('Locale : %s', $this->sdkConfig->getLocale()));
            $this->line(sprintf('Format : %s', $this->sdkConfig->getFormat()));
            $this->line(sprintf("LangPaths: %s", implode(", ", $this->sdkConfig->getLangPaths()->toArray())));
        }

        $this->comment('Process ...');
    }

    protected function endInfo($result)
    {
        $result ? $this->info(sprintf('Success! We process %d abstractions', count($result))) :
            $this->info('Success');
    }
}
