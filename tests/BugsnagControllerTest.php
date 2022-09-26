<?php

namespace Violet88\BugsnagModule\Tests;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\SapphireTest;
use Violet88\BugsnagModule\Bugsnag;
use Violet88\BugsnagModule\BugsnagController;

/**
 * @covers \Violet88\BugsnagModule\BugsnagController
 * @covers \Violet88\BugsnagModule\Bugsnag
 */
class BugsnagControllerTest extends SapphireTest
{
    protected static $fixture_file = 'fixtures.yml';

    public function testCommand()
    {
        Injector::nest();

        $testSnag = $this->getMockBuilder(Bugsnag::class)
            ->setMethods(['notifyBuild', 'setAppVersion'])
            ->getMock();

        $testSnag->expects($this->once())
            ->method('notifyBuild')
            ->willReturnSelf();
        $testSnag->expects($this->once())
            ->method('setAppVersion')
            ->willReturnSelf();

        Injector::inst()->registerService($testSnag, Bugsnag::class);
        BugsnagController::create()->build();

        Injector::unnest();
    }

    public function testInitial()
    {
        Injector::nest();

        //Making a 'mock' client so no useless errors are sent to Bugsnag.
        $testSnag = $this->getMockBuilder(Bugsnag::class)
            ->setMethods(['sendException'])
            ->getMock();

        Injector::inst()->registerService($testSnag, Bugsnag::class);

        $testSnag->expects($this->once())
            ->method('sendException')
            ->willReturnSelf();

        BugsnagController::create()->initial();
        Injector::unnest();
    }
}
