<?php

namespace KonfioSdk\Services;

use PHPUnit\Framework\TestCase;

class AwsServiceTest extends TestCase
{
    public function setUp()
    {
        putenv('ENV=dev');
    }

    public function testGetAwsSdkInstance()
    {
        $sp = new ServiceProvider;

        $aws = $sp->getService('aws')->setSdk(\KonfioSdk\Test\Mock\AwsSdkMock::class);

        $this->assertInstanceOf('KonfioSdk\Test\Mock\AwsSdkMock', $aws->getSdk());
    }

    public function testGetAwsS3ClientInstance()
    {
        $sp = new ServiceProvider;

        $aws = $sp->getService('aws')->setSdk(\KonfioSdk\Test\Mock\AwsSdkMock::class);

        $config = $sp->getConfig()['aws'];

        $aws->setSdkConfig($config);

        $s3Client = $aws->getSdk()->createS3();

        $this->assertInstanceOf('KonfioSdk\Test\Mock\S3ClientMock', $s3Client);
    }
}
