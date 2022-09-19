<?php

namespace Violet88\BugsnagModule;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;

class BugsnagController extends Controller
{
    private static $allowed_actions = [
        'build',
        'initial'
    ];

    /**
     * Simply sends the given release to Bugsnag, to be used in CLI.
     *
     * @throws NotFoundExceptionInterface
     */
    public function build(): HTTPResponse
    {
        $repository = $_GET['repository'] ?? null;
        $revision = $_GET['revision'] ?? null;
        $provider = $_GET['provider'] ?? null;
        $builderName = $_GET['builderName'] ?? null;
        $appVersion = $_GET['revision'] ?? null;

        $bugsnag = Injector::inst()->get(Bugsnag::class);
        $bugsnag
            ->setAppVersion($appVersion)
            ->notifyBuild($repository, $revision, $provider, $builderName);

        $response = new HTTPResponse();
        $response->setStatusCode(200);
        return $response;
    }

    public function initial()
    {
        $bugsnag = Injector::inst()->get(Bugsnag::class);
        $bugsnag->sendException(new RuntimeException("Test error"));
    }
}
