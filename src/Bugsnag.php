<?php

namespace Violet88\BugsnagModule;

use Bugsnag\Client;
use Bugsnag\Report;
use Composer\InstalledVersions;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Security\Security;

class Bugsnag
{
    use Configurable;

    public Client $bugsnag;

    protected $EXTRA_OPTIONS = array();

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
    private static $ACTIVE;

    public function __construct()
    {
        $this->bugsnag = Client::make(Config::inst()->get('Violet88\BugsnagModule\Bugsnag', 'API_KEY'));
        $this->bugsnag->setAppType('Silverstripe');
    }

    public function reset()
    {
        $this->EXTRA_OPTIONS = [];
    }

    public function getStandardSeverity()
    {
        return Config::inst()->get('Violet88\BugsnagModule\Bugsnag', 'STANDARD_SEVERITY');
    }

    public function getExtraOptions()
    {
        return $this->EXTRA_OPTIONS;
    }

    public function addExtraOption($key, $value)
    {
        $this->EXTRA_OPTIONS[$key] = $value;
        return $this;
    }

    public function removeExtraOption($key)
    {
        unset($this->EXTRA_OPTIONS[$key]);
        return $this;
    }

    /**
     * @return Client
     */
    public function getBugsnag(): Client
    {
        return $this->bugsnag;
    }

    public function sendException(
        \Exception $exception,
        string $severity = null,
        $resetExtraOptions = true,
        $handled = true
    ) {
        if (Config::inst()->get('Violet88\BugsnagModule\Bugsnag', 'ACTIVE')) {
            if (empty($severity)) {
                $severity = $this->getStandardSeverity();
            }
            if ($handled) {
                $this->getBugsnag()->notifyException(
                    $exception,
                    function (Report $report) use ($severity, $handled) {
                        $this->notifyCallback($report, $severity);
                    }
                );
            } else {
                $this->getBugsnag()->notify(
                    Report::fromPHPThrowable(
                        $this->getBugsnag()->getConfig(),
                        $exception
                    )->setUnhandled(true),
                    function (Report $report) use ($severity) {
                        $this->notifyCallback($report, $severity);
                    }
                );
            }
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

    public function addUserInfo($bool = true)
    {
        if ($bool) {
            if ($member = Security::getCurrentUser()) {
                $this->addExtraOption('User', array(
                    'Email' => $member->Email,
                    'FirstName' => $member->FirstName,
                    'Surname' => $member->Surname,
                    'ID' => $member->ID,
                    'Groups' => $member->Groups()?->column('Title'),
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

    public function addVersion(bool $bool = true)
    {
        if ($bool) {
            $version = InstalledVersions::getRootPackage()['pretty_version'];
            $this->setAppVersion($version);
        } else {
            $this->removeExtraOption('Version');
        }
        return $this;
    }

    public function addPackages(bool $bool = true)
    {
        if ($bool) {
            $packages = InstalledVersions::getInstalledPackages();
            $this->addExtraOption('Packages', $packages);
        } else {
            $this->removeExtraOption('Packages');
        }
        return $this;
    }

    public function addPackagesWithVersions(bool $bool = true)
    {
        if ($bool) {
            $packages = InstalledVersions::getInstalledPackages();
            $packagesWithVersions = [];
            foreach ($packages as $package) {
                $packagesWithVersions[$package] = InstalledVersions::getPrettyVersion($package);
            }
            $this->addExtraOption('Packages', $packagesWithVersions);
        } else {
            $this->removeExtraOption('Packages');
        }
        return $this;
    }

    public function notifyBuild($repository, $revision, $provider, $builderName)
    {
        $this->bugsnag->build($repository, $revision, $provider, $builderName);
        return $this;
    }
}
