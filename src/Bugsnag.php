<?php

namespace Violet88\BugsnagModule;

use Bugsnag\Client;
use Bugsnag\Report;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Security\Security;

class Bugsnag
{
    use Configurable;

    public Client $bugsnag;

    protected $extraOptions = array();

    /**
     * @config
     */
    private static $API_KEY;

    /**
     * @config
     */
    private static $STANDARD_SEVERITY;

    /**
     * @config
     */
    private static $active;

    public function __construct()
    {
        $this->bugsnag = Client::make($this->config()->get('API_KEY'));
    }

    public function reset()
    {
        $this->extraOptions = [];
    }

    private function getStandardSeverity()
    {
        return $this->config()->get('STANDARD_SEVERITY');
    }

    private function getExtraOptions()
    {
        return $this->extraOptions;
    }

    public function addExtraOption($key, $value)
    {
        $this->extraOptions[$key] = $value;
        return $this;
    }

    public function removeExtraOption($key)
    {
        unset($this->extraOptions[$key]);
        return $this;
    }

    public function sendException(\Exception $exception, string $severity = null, $resetExtraOptions = true)
    {
        if ($this->config()->get('active')) {
            if (empty($severity)) {
                $severity = $this->getStandardSeverity();
            }
            $this->bugsnag->notifyException($exception, function (Report $report) use ($severity) {
                $report->setSeverity($severity);
                $report->setMetaData($this->getExtraOptions());
            });
            if ($resetExtraOptions) {
                $this->reset();
            }
        }
    }

    public function addUserInfo($bool)
    {
        if ($bool) {
            if ($member = Security::getCurrentUser()) {
                $this->addExtraOption('User', array(
                    'Email' => $member->Email,
                    'FirstName' => $member->FirstName,
                    'Surname' => $member->Surname,
                    'ID' => $member->ID
                ));
            }
        } else {
            $this->removeExtraOption('User');
        }
        return $this;
    }

    public function addAppVersion($version)
    {
        $this->bugsnag->setAppVersion($version);
        return $this;
    }

    public function addAppType($type)
    {
        $this->bugsnag->setAppType($type);
        return $this;
    }

    public function addReleaseStage($stage)
    {
        $this->bugsnag->setReleaseStage($stage);
        return $this;
    }

    public function sendError($error)
    {
        $this->bugsnag->notifyError('Error', $error);
    }

    public function notifyBuild($repository, $revision, $provider, $builderName)
    {
        $this->bugsnag->build($repository, $revision, $provider, $builderName);
    }
}
