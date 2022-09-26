<?php

namespace Violet88\BugsnagModule;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class BugsnagLogger extends AbstractProcessingHandler
{
    private Bugsnag $bugsnag;

    public function __construct(Bugsnag $client, $level = Logger::WARNING, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->bugsnag = $client;
    }


    /**
     * Function that gets called when a log is getting written. This function will send the log to Bugsnag.
     *
     * @param array $record
     * @return void
     */
    protected function write(array $record)
    {
        if (isset($record['context'])) {
            if (!isset($record['context']['exception'])) {
                $this->getBugsnag()->sendError($record['message']);
                return;
            }
            //check if $record['context']['exception'] is an instance of \Exception
            if ($record['context']['exception'] instanceof \Exception) {
                $this->getBugsnag()
                    ->addUserInfo()
                    ->addPackagesWithVersions()
                    ->sendException(
                        $record['context']['exception'],
                        $this->levelToSeverity($record['level']),
                        true,
                        false
                    );
                return;
            }
            $this->getBugsnag()->sendError($record['message']);
        }
    }

    public function getBugsnag(): Bugsnag
    {
        return $this->bugsnag;
    }

    /**
     * Convert Monolog Level to Bugsnag Severity.
     *
     * @param int $level
     * @return string
     */
    private function levelToSeverity($level)
    {
        switch ($level) {
            case 100:
            case 200:
            case 250:
                return Severity::INFO;
            case 300:
                return Severity::WARNING;
            case 400:
            case 500:
            case 550:
            case 600:
                return Severity::ERROR;
        }
        return Severity::INFO;
    }
}
