<?php

namespace Violet88\BugsnagModule;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;

class BugsnagController extends Controller
{
    private static $allowed_actions = [
        'index'
    ];

    public function index()
    {
        $repository = $_GET['repository'] ?? null;
        $revision = $_GET['revision'] ?? null;
        $provider = $_GET['provider'] ?? null;
        $builderName = $_GET['builderName'] ?? null;

        Injector::inst()->get(Bugsnag::class)->notifyBuild($repository, $revision, $provider, $builderName);
    }
}
