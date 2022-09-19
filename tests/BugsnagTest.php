<?php

namespace Violet88\BugsnagModule\Tests;

use Composer\InstalledVersions;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Security;
use Violet88\BugsnagModule\Bugsnag;

/**
 * @covers \Violet88\BugsnagModule\Bugsnag
 */
class BugsnagTest extends SapphireTest
{
    public function testGetExtraOption()
    {
        $stub = $this->getMockBuilder(Bugsnag::class)
            ->setMethods(['getExtraOptions'])
            ->getMock();

        $extraOptions = [
            'key' => 'value'
        ];

        $stub->method('getExtraOptions')
            ->willReturn($extraOptions);

        $this->assertEquals($extraOptions, $stub->getExtraOptions());
    }

    public function testAddExtraOption()
    {
        $bugsnag = new Bugsnag();
        $bugsnag->addExtraOption('key', 'value');
        $this->assertEquals(['key' => 'value'], $bugsnag->getExtraOptions());
    }

    public function testAddExtraOptionReturnsSelfRef()
    {
        $bugsnag = new Bugsnag();
        $this->assertEquals($bugsnag, $bugsnag->addExtraOption('key', 'value'));
    }

    public function testRemoveExtraOption()
    {
        $bugsnag = new Bugsnag();
        $bugsnag->addExtraOption('key', 'value');
        $bugsnag->removeExtraOption('key');
        $this->assertEquals([], $bugsnag->getExtraOptions());
    }

    public function testRemoveExtraOptionReturnsSelfRef()
    {
        $bugsnag = new Bugsnag();
        $this->assertEquals($bugsnag, $bugsnag->removeExtraOption('key'));
    }

    public function testGetStandardSeverity()
    {
        $bugsnag = new Bugsnag();
        $this->assertEquals(
            Environment::getEnv('BUGSNAG_STANDARD_SEVERITY'),
            $bugsnag->getStandardSeverity()
        );
    }

    public function testReset()
    {
        $bugsnag = new Bugsnag();
        $bugsnag->addExtraOption('key', 'value');
        $this->assertEquals(['key' => 'value'], $bugsnag->getExtraOptions());
        $bugsnag->reset();
        $this->assertEquals([], $bugsnag->getExtraOptions());
    }

    public function testAddUserInfo()
    {
        $bugsnag = new Bugsnag();

        $memberMock = $this->getMockBuilder('SilverStripe\Security\Member')
            ->setMethods(['ID', 'Email', 'FirstName', 'Surname', 'Groups'])
            ->getMock();

        $groupMock = $this->getMockBuilder('SilverStripe\Security\Group')
            ->setMethods(['column'])
            ->getMock();

        $groupMock->method('column')
            ->willReturn(['group1', 'group2']);

        $memberMock->data()->ID = 1;

        $memberMock->data()->Email = 'test@test.nl';

        $memberMock->data()->FirstName = 'Test';

        $memberMock->data()->Surname = 'Test';

        $memberMock->method('Groups')
            ->willReturn($groupMock);

        Security::setCurrentUser($memberMock);

        $bugsnag->addUserInfo(true);

        $this->assertEquals([
            'User' => [
                'ID' => 1,
                'Email' => 'test@test.nl',
                'FirstName' => 'Test',
                'Surname' => 'Test',
                'Groups' => ['group1', 'group2']
            ]
        ], $bugsnag->getExtraOptions());

        $bugsnag->addUserInfo(false);
        $this->assertEquals([], $bugsnag->getExtraOptions());
    }

    public function testSendException()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['notifyException'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;
        $bugsnag->addExtraOption('key', 'value');
        $this->assertEquals(['key' => 'value'], $bugsnag->getExtraOptions());

        $clientMock->expects($this->once())
            ->method('notifyException')
            ->willReturn(true);

        $bugsnag->sendException(new \Exception('test'));
        $this->assertEquals([], $bugsnag->getExtraOptions());
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('Violet88\BugsnagModule\Bugsnag');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testNotifyCallback()
    {
        $notify = self::getMethod('notifyCallback');
        $obj = new Bugsnag();
        $report = $this->getMockBuilder('Bugsnag\Report')
            ->disableOriginalConstructor()
            ->setMethods(['setSeverity', 'setMetaData'])
            ->getMock();

        $report->expects($this->once())
            ->method('setSeverity');

        $report->expects($this->once())
            ->method('setMetaData');

        $notify->invokeArgs($obj, [$report, 'info']);
    }

    public function testSendExceptionCallsGetStandardSeverity()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['notifyException'])
            ->getMock();

        $bugsnag = $this->getMockBuilder('Violet88\BugsnagModule\Bugsnag')
            ->setMethods(['getStandardSeverity', 'getBugsnag'])
            ->getMock();

        $bugsnag->method('getBugsnag')
            ->willReturn($clientMock);

        $bugsnag->expects($this->once())
            ->method('getStandardSeverity')
            ->willReturn('error');

        $bugsnag->sendException(new \Exception('test'));
    }

    public function testSendExceptionCallsNotifyWhenNotHandled()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['notify'])
            ->getMock();

        $bugsnag = $this->getMockBuilder('Violet88\BugsnagModule\Bugsnag')
            ->setMethods(['getStandardSeverity', 'getBugsnag'])
            ->getMock();

        $bugsnag->method('getBugsnag')
            ->willReturn($clientMock);

        $clientMock->expects($this->once())
            ->method('notify')
            ->willReturn(true);

        $bugsnag->sendException(new \Exception('test'), 'info', true, false);
    }

    public function testSetAppType()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['setAppType'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        $clientMock->expects($this->once())
            ->method('setAppType');

        $bugsnag->setAppType('test');
    }

    public function testSetAppTypeReturnsSelfRef()
    {
        $bugsnag = new Bugsnag();
        $result = $bugsnag->setAppType('test');
        $this->assertEquals($result, $bugsnag);
    }

    public function testSetAppVersion()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['setAppVersion'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        $clientMock->expects($this->once())
            ->method('setAppVersion');

        $bugsnag->setAppVersion('1.0.0');
    }

    public function testSetAppVersionReturnsSelfRef()
    {
        $bugsnag = new Bugsnag();
        $result = $bugsnag->setAppVersion('1.0.0');
        $this->assertEquals($result, $bugsnag);
    }

    public function testSetReleaseStage()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['setReleaseStage'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        $clientMock->expects($this->once())
            ->method('setReleaseStage');

        $bugsnag->setReleaseStage('0.1');
    }

    public function testSetReleaseStageReturnsSelfRef()
    {
        $bugsnag = new Bugsnag();
        $result = $bugsnag->setReleaseStage('0.1');
        $this->assertEquals($result, $bugsnag);
    }

    public function testSendError()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['notifyError'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        $clientMock->expects($this->once())
            ->method('notifyError')
            ->willReturn(true);

        $bugsnag->sendError('test');
        $this->assertEquals([], $bugsnag->getExtraOptions());
    }

    public function testSetEndpoint()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['setNotifyEndpoint'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        $clientMock->expects($this->once())
            ->method('setNotifyEndpoint');

        $bugsnag->setEndpoint('/test');
    }

    public function testSetEndpointReturnsSelfRef()
    {
        $bugsnag = new Bugsnag();
        $result = $bugsnag->setEndpoint('/test');
        $this->assertEquals($result, $bugsnag);
    }

    public function testNotifyBuild()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['build'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        $clientMock->expects($this->once())
            ->method('build')
            ->willReturn(true);

        $bugsnag->notifyBuild('test@github', 'test', '1.0.0', 'test');
    }

    public function testAddVersion()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['setAppVersion'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        $clientMock->expects($this->once())
            ->method('setAppVersion')
            ->with(InstalledVersions::getRootPackage()['pretty_version']);

        $bugsnag->addVersion();

        $bugsnag->addVersion(false);

        $this->assertEquals([], $bugsnag->getExtraOptions());
    }

    public function testAddPackages()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['setAppVersion'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        self::assertEquals([], $bugsnag->getExtraOptions());

        $bugsnag->addPackages();

        self::assertEquals(InstalledVersions::getInstalledPackages(), $bugsnag->getExtraOptions()['Packages']);

        $bugsnag->addPackages(false);

        self::assertEquals([], $bugsnag->getExtraOptions());
    }

    public function testAddPackagesWithVersions()
    {
        $bugsnagConfigMock = $this->getMockBuilder('Bugsnag\Configuration')
            ->setConstructorArgs(['API_KEY'])
            ->getMock();

        $clientMock = $this->getMockBuilder('Bugsnag\Client')
            ->setConstructorArgs([$bugsnagConfigMock])
            ->setMethods(['setAppVersion'])
            ->getMock();

        $bugsnag = new Bugsnag();
        $bugsnag->bugsnag = $clientMock;

        self::assertEquals([], $bugsnag->getExtraOptions());

        $bugsnag->addPackagesWithVersions();

        $bugsnag->addPackagesWithVersions(false);

        self::assertEquals([], $bugsnag->getExtraOptions());
    }
}
