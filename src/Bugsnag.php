<?php

namespace Violet88\BugsnagModule;

use Bugsnag\Client;
use Bugsnag\Report;
use Composer\InstalledVersions;
use Exception;
use SilverStripe\Core\Environment;
use SilverStripe\Security\Security;

class Bugsnag
{

    public Client $bugsnag;

    protected $EXTRA_OPTIONS = array();

    public function __construct()
    {
        $this->bugsnag = Client::make(Environment::getEnv('BUGSNAG_API_KEY'));
        $this->bugsnag->setAppType('Silverstripe');
        $this->bugsnag->setReleaseStage(Environment::getEnv('BUGSNAG_RELEASE_STAGE') ?? 'development');
    }

    /**
     * Reset the custom metadata
     *
     * @return void
     */
    public function reset()
    {
        $this->EXTRA_OPTIONS = [];
    }

    /**
     * Get the standard severity set in the config.
     *
     * @return mixed
     */
    public function getStandardSeverity()
    {
        return Environment::getEnv('BUGSNAG_STANDARD_SEVERITY');
    }

    /**
     * Get the current custom metadata
     *
     * @return array
     */
    public function getExtraOptions()
    {
        return $this->EXTRA_OPTIONS;
    }

    /**
     * Add a key value pair to the metadata
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addExtraOption($key, $value)
    {
        $this->EXTRA_OPTIONS[$key] = $value;
        return $this;
    }

    /**
     * Remove a key value pair from the metadata
     *
     * @param $key
     * @return $this
     */
    public function removeExtraOption($key)
    {
        unset($this->EXTRA_OPTIONS[$key]);
        return $this;
    }

    /**
     * Get the Bugsnag client
     *
     * @return Client
     */
    public function getBugsnag(): Client
    {
        return $this->bugsnag;
    }

    /**
     * This method send the exception to Bugsnag. Perform any configuration to your error report BEFORE you call this
     * method.
     *
     * @param Exception $exception
     * @param string|null $severity
     * @param bool $resetExtraOptions
     * @param bool $handled
     * @return void
     */
    public function sendException(
        Exception $exception,
        string $severity = null,
        bool $resetExtraOptions = true,
        bool $handled = true
    ) {
        $active = Environment::getEnv('BUGSNAG_ACTIVE');
        if ($active === "true") {
            if (empty($severity)) {
                $severity = $this->getStandardSeverity();
            }
            if ($handled) {
                $this->getBugsnag()->notifyException(
                    $exception,
                    function (Report $report) use ($severity) {
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
     * This method is for internal use only. It is called by sendException() to configure the error report
     *
     * @param Report $report
     * @param $severity
     * @return void
     */
    protected function notifyCallback(Report $report, $severity)
    {
        $report->setSeverity($severity);
        $report->setMetaData($this->getExtraOptions());
    }

    /**
     * Add the logged-in user to the error report. This user is automatically retrieved. When given no parameter,
     * it will add the user to the error report. When given false, it will remove the user from the error report.
     *
     * @param $bool
     * @return $this
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
                    'Groups' => $member->Groups() ? $member->Groups()->column('Title') : [],
                ));
            }
        } else {
            $this->removeExtraOption('User');
        }
        return $this;
    }

    /**
     * Add the given version to the error report as the app version.
     *
     * @param $version
     * @return $this
     */
    public function setAppVersion($version)
    {
        $this->bugsnag->setAppVersion($version);
        return $this;
    }

    /**
     * Add the given type to the error report as the app type.
     *
     * @param $type
     * @return $this
     */
    public function setAppType($type)
    {
        $this->bugsnag->setAppType($type);
        return $this;
    }

    /**
     * Add the given stage to the error report as the release stage of the application.
     *
     * @param $version
     * @return $this
     */
    public function setReleaseStage($stage)
    {
        $this->bugsnag->setReleaseStage($stage);
        return $this;
    }

    /**
     * Send an error with the given message to Bugsnag
     *
     * @param $error
     * @return void
     */
    public function sendError($error)
    {
        $active = Environment::getEnv('BUGSNAG_ACTIVE');
        if ($active === "true") {
            $this->bugsnag->notifyError('Error', $error);
        }
    }

    /**
     * Set the endpoint to which the error report is sent. This is useful for on premise bugsnag.
     *
     * @param $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->bugsnag->setNotifyEndpoint($endpoint);
        return $this;
    }

    /**
     * Add the version of the application, set in composer.json, to the error report. When given no parameter,
     * it will add the version to the error report. When given false, it will remove the version from the error report.
     *
     * @param bool $bool
     * @return $this
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
     * Add the installed packages, without versions, to the error report. When given no parameter,
     * it will add the packages to the error report. When given false,
     * it will remove the packages from the error report.
     *
     * @param bool $bool
     * @return $this
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
     * Add the installed packages, with their versions, to the error report. When given no parameter,
     * it will add the packages to the error report. When given false,
     * it will remove the packages from the error report.
     *
     * @param bool $bool
     * @return $this
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
     * Send a new build release to Bugsnag. This is useful for matching versions with releases.
     *
     * @param $repository
     * @param $revision
     * @param $provider
     * @param $builderName
     * @return $this
     */
    public function notifyBuild($repository, $revision, $provider, $builderName)
    {
        $this->bugsnag->build($repository, $revision, $provider, $builderName);
        return $this;
    }
}
