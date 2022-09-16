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

    /**
     * @return void
     * Reset the custom metadata
     */
    public function reset()
    {
        $this->EXTRA_OPTIONS = [];
    }

    /**
     * @return mixed
     * Get the standard severity set in the config.
     */
    public function getStandardSeverity()
    {
        return Config::inst()->get('Violet88\BugsnagModule\Bugsnag', 'STANDARD_SEVERITY');
    }

    /**
     * @return array
     * Get the current custom metadata
     */
    public function getExtraOptions()
    {
        return $this->EXTRA_OPTIONS;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * Add a key value pair to the metadata
     */
    public function addExtraOption($key, $value)
    {
        $this->EXTRA_OPTIONS[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return $this
     * Remove a key value pair from the metadata
     */
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

    /**
     * @param \Exception $exception
     * @param string|null $severity
     * @param bool $resetExtraOptions
     * @param bool $handled
     * @return void
     * This method send the exception to Bugsnag. Perform any configuration to your error report BEFORE you call this
     * method.
     */
    public function sendException(
        \Exception $exception,
        string $severity = null,
        bool $resetExtraOptions = true,
        bool $handled = true
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

    /**
     * @param Report $report
     * @param $severity
     * @return void
     * This method is for internal use only. It is called by sendException() to configure the error report
     */
    protected function notifyCallback(Report $report, $severity)
    {
        $report->setSeverity($severity);
        $report->setMetaData($this->getExtraOptions());
    }

    /**
     * @param $bool
     * @return $this
     * Add the logged-in user to the error report. This user is automatically retrieved. When given no parameter,
     * it will add the user to the error report. When given false, it will remove the user from the error report.
     */
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

    /**
     * @param $version
     * @return $this
     * Add the given version to the error report as the app version.
     */
    public function setAppVersion($version)
    {
        $this->bugsnag->setAppVersion($version);
        return $this;
    }

    /**
     * @param $type
     * @return $this
     * Add the given type to the error report as the app type.
     */
    public function setAppType($type)
    {
        $this->bugsnag->setAppType($type);
        return $this;
    }

    /**
     * @param $version
     * @return $this
     * Add the given stage to the error report as the release stage of the application.
     */
    public function setReleaseStage($stage)
    {
        $this->bugsnag->setReleaseStage($stage);
        return $this;
    }

    /**
     * @param $error
     * @return void
     * Send an error with the given message to Bugsnag
     */
    public function sendError($error)
    {
        $this->bugsnag->notifyError('Error', $error);
    }

    /**
     * @param $endpoint
     * @return $this
     * Set the endpoint to which the error report is sent. This is useful for on premise bugsnag.
     */
    public function setEndpoint($endpoint)
    {
        $this->bugsnag->setNotifyEndpoint($endpoint);
        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     * Add the version of the application, set in composer.json, to the error report. When given no parameter,
     * it will add the version to the error report. When given false, it will remove the version from the error report.
     */
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

    /**
     * @param bool $bool
     * @return $this
     * Add the installed packages, without versions, to the error report. When given no parameter,
     * it will add the packages to the error report. When given false,
     * it will remove the packages from the error report.
     */
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

    /**
     * @param bool $bool
     * @return $this
     * Add the installed packages, with their versions, to the error report. When given no parameter,
     * it will add the packages to the error report. When given false,
     * it will remove the packages from the error report.
     */
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

    /**
     * @param $repository
     * @param $revision
     * @param $provider
     * @param $builderName
     * @return $this
     * Send a new build release to Bugsnag. This is useful for matching versions with releases.
     */
    public function notifyBuild($repository, $revision, $provider, $builderName)
    {
        $this->bugsnag->build($repository, $revision, $provider, $builderName);
        return $this;
    }
}
