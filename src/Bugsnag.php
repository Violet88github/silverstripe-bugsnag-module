<?php

namespace Violet88\BugsnagModule;

use Bugsnag\Client;
use Bugsnag\Report;
use SilverStripe\Core\Config\Config;
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
        $this->bugsnag = Client::make(Config::inst()->get('Violet88\BugsnagModule\Bugsnag', 'API_KEY'));
    }

    public function reset()
    {
        $this->extraOptions = [];
    }

    public function getStandardSeverity()
    {
        return Config::inst()->get('Violet88\BugsnagModule\Bugsnag', 'STANDARD_SEVERITY');
    }

    public function getExtraOptions()
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

    /**
    * @return Client
    */
    public function getBugsnag(): Client
    {
        return $this->bugsnag;
    }

    public function sendException(\Exception $exception, string $severity = null, $resetExtraOptions = true)
    {
        if (Config::inst()->get('Violet88\BugsnagModule\Bugsnag', 'active')) {
            if (empty($severity)) {
                $severity = $this->getStandardSeverity();
            }
            $this->getBugsnag()->notifyException($exception, function (Report $report) use ($severity) {
                $this->notifyCallback($report, $severity);
            });
            if ($resetExtraOptions) {
                $this->reset();
            }
        }
    }

    protected function notifyCallback(Report $report, $severity)
    {
        $report->setSeverity($severity);
        $report->setMetaData($this->getExtraOptions());
    }

    public function addUserInfo($bool)
    {
        if ($bool) {
            if ($member = Security::getCurrentUser()) {
                $this->addExtraOption('User', array(
                    'Email' => $member->Email,
                    'FirstName' => $member->FirstName,
                    'Surname' => $member->Surname,
                    'ID' => $member->ID,
                    'Groups' => $member->Groups()->column('Title'),
                ));
            }
        } else {
            $this->removeExtraOption('User');
        }
        return $this;
    }

    public function setAppVersion($version)
    {
        $this->bugsnag->setAppVersion($version);
        return $this;
    }

    public function setAppType($type)
    {
        $this->bugsnag->setAppType($type);
        return $this;
    }

    public function setReleaseStage($stage)
    {
        $this->bugsnag->setReleaseStage($stage);
        return $this;
    }

    public function sendError($error)
    {
        $this->bugsnag->notifyError('Error', $error);
    }

    public function setEndpoint($endpoint)
    {
        $this->bugsnag->setNotifyEndpoint($endpoint);
        return $this;
    }

    public function notifyBuild($repository, $revision, $provider, $builderName)
    {
        $this->bugsnag->build($repository, $revision, $provider, $builderName);
        return $this;
    }
}
