<?php

namespace KonfioSdk\Services;

use PHPUnit\Framework\TestCase;
use KonfioSdk\Exceptions\ServiceException;

class ServiceProviderTest extends TestCase
{
    protected $sp;

    public function setUp()
    {
        putenv('ENV=dev');

        $this->sp = new ServiceProvider;
    }

    public function testBadMethodCallName()
    {
        $message = 'Test that the ServiceProvider throws a ServiceException if a bad method call is used';

        $this->expectException(ServiceException::class);

        $this->sp->getService('foo');

    }

    public function testSetConfigPathMethodReturnsException()
    {
        $message = 'Test that the setConfigPath returns an exception if the file does not exist';

        $this->expectException(\Exception::class);

        $this->sp->setConfigPath(dirname(__DIR__, 1) . "/config/serviceprovider.foo");
    }

    public function testGetJSONServiceConfigByName()
    {
        $message = 'Test that the ServiceProvider gets an Aws config array from a serviceprovider.ENV.json file';

        $env = getenv('ENV');

        $this->sp->setConfigPath(dirname(__DIR__, 1) . "/config/serviceprovider.{$env}.json");

        $config = $this->sp->getConfig()['aws'];

        $this->assertArrayHasKey('region', $config);
        $this->assertArrayHasKey('version', $config);
        $this->assertArrayHasKey('credentials', $config);
    }

    public function testGetPHPServiceConfigByName()
    {
        $message = 'Test that the ServiceProvider gets an Aws config array from a serviceprovider.ENV.php file';

        $env = getenv('ENV');

        $this->sp->setConfigPath(dirname(__DIR__, 1) . "/config/serviceprovider.{$env}.php");

        $config = $this->sp->getConfig()['aws'];

        $this->assertArrayHasKey('region', $config);
        $this->assertArrayHasKey('version', $config);
        $this->assertArrayHasKey('credentials', $config);
    }

    public function testGetServiceConfigByFileName()
    {
        $message = 'Test that the ServiceProvider gets an Aws config array from a aws.ENV.php file';

        $env = getenv('ENV');

        $this->sp->setConfigPath(dirname(__DIR__, 1) . "/config/aws.{$env}.php");

        $config = $this->sp->getConfig();

        $this->assertArrayHasKey('region', $config);
        $this->assertArrayHasKey('version', $config);
        $this->assertArrayHasKey('credentials', $config);
    }
}
