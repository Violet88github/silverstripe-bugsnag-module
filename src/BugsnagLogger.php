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


    protected function write(array $record)
    {
        if (isset($record['context'])) {
            if (empty($record['exception'])) {
                $this->bugsnag->sendError($record['message']);
                return;
            }
            $this->bugsnag->sendException($record['context']['exception'], $this->levelToSeverity($record['level']));
        }
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
    }
}
