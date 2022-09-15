<?php

namespace Violet88\BugsnagModule\Tests;

use SilverStripe\Dev\SapphireTest;
use Violet88\BugsnagModule\Bugsnag;
use Violet88\BugsnagModule\BugsnagLogger;

/**
 * @covers \Violet88\BugsnagModule\BugsnagLogger
 * @covers \Violet88\BugsnagModule\Bugsnag
 */
class BugsnagLoggerTest extends SapphireTest
{
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('Violet88\BugsnagModule\BugsnagLogger');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testWriteSendsError()
    {
        $write = self::getMethod('write');
        $bugsnagMock = $this->getMockBuilder('Violet88\BugsnagModule\Bugsnag')
            ->setMethods(['sendException', 'sendError'])
            ->getMock();
        $bugsnagMock->expects($this->exactly(2))
            ->method('sendError');
        $obj = $this->getMockBuilder('Violet88\BugsnagModule\BugsnagLogger')
            ->setConstructorArgs([$bugsnagMock])
            ->setMethods(['getBugsnag'])
            ->getMock();

        $obj->expects($this->exactly(2))
            ->method('getBugsnag')
            ->willReturn($bugsnagMock);
        $args = [
            'message' => 'test',
            'context' => [
                'message' => 'test'
            ],
            'level' => 300
        ];

        $args2 = [
            'message' => 'test',
            'context' => [
                'exception' => 'test'
            ],
            'level' => 300
        ];

        $write->invokeArgs($obj, [$args]);
        $write->invokeArgs($obj, [$args2]);
    }

    public function testWriteSendsException()
    {
        $write = self::getMethod('write');
        $bugsnagMock = $this->getMockBuilder('Violet88\BugsnagModule\Bugsnag')
            ->setMethods(['sendException', 'sendError'])
            ->getMock();
        $bugsnagMock->expects($this->once())
            ->method('sendException');
        $obj = $this->getMockBuilder('Violet88\BugsnagModule\BugsnagLogger')
            ->setConstructorArgs([$bugsnagMock])
            ->setMethods(['getBugsnag'])
            ->getMock();

        $obj->expects($this->once())
            ->method('getBugsnag')
            ->willReturn($bugsnagMock);
        $args = [
            'message' => 'test',
            'context' => [
                'exception' => new \Exception('test')
            ],
            'level' => 300
        ];
        $write->invokeArgs($obj, [$args]);
    }

    public function testWriteSendsErrorWithCorrectSeverity100()
    {
        $write = self::getMethod('write');
        $exception = new \Exception('test');
        $bugsnagMock = $this->getMockBuilder('Violet88\BugsnagModule\Bugsnag')
            ->setMethods(['sendException', 'sendError'])
            ->getMock();
        $bugsnagMock->expects($this->once())
            ->method('sendException')
            ->with($this->anything(), $this->equalTo('info'), $this->anything());
        $obj = $this->getMockBuilder('Violet88\BugsnagModule\BugsnagLogger')
            ->setConstructorArgs([$bugsnagMock])
            ->setMethods(['getBugsnag'])
            ->getMock();

        $obj->expects($this->once())
            ->method('getBugsnag')
            ->willReturn($bugsnagMock);
        $args = [
            'message' => 'test',
            'context' => [
                'exception' => $exception
            ],
            'level' => 100
        ];
        $write->invokeArgs($obj, [$args]);
    }

    public function testWriteSendsErrorWithCorrectSeverity600()
    {
        $write = self::getMethod('write');
        $exception = new \Exception('test');
        $bugsnagMock = $this->getMockBuilder('Violet88\BugsnagModule\Bugsnag')
            ->setMethods(['sendException', 'sendError'])
            ->getMock();
        $bugsnagMock->expects($this->once())
            ->method('sendException')
            ->with($this->anything(), $this->equalTo('error'), $this->anything());
        $obj = $this->getMockBuilder('Violet88\BugsnagModule\BugsnagLogger')
            ->setConstructorArgs([$bugsnagMock])
            ->setMethods(['getBugsnag'])
            ->getMock();

        $obj->expects($this->once())
            ->method('getBugsnag')
            ->willReturn($bugsnagMock);
        $args = [
            'message' => 'test',
            'context' => [
                'exception' => $exception
            ],
            'level' => 600
        ];
        $write->invokeArgs($obj, [$args]);
    }

    public function testGetBugsnag()
    {
        $bugsnagMock = $this->getMockBuilder('Violet88\BugsnagModule\Bugsnag')
            ->setMethods(['sendException', 'sendError'])
            ->getMock();
        $obj = new BugsnagLogger($bugsnagMock);
        $this->assertEquals($bugsnagMock, $obj->getBugsnag());
    }
}
