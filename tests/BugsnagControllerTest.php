<?php

namespace Violet88\BugsnagModule\Tests;

use SilverStripe\Dev\FunctionalTest;

/**
 * @covers \Violet88\BugsnagModule\BugsnagController
 * @covers \Violet88\BugsnagModule\Bugsnag
 */
class BugsnagControllerTest extends FunctionalTest
{
    public function testCommand()
    {
        $response = $this->get('bugsnag_build');
        $this->assertEquals(200, $response->getStatusCode());
    }

}
