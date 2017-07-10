<?php

namespace Solicitud\Service;

use KonfioSdk\Services\ServiceProvider;
use KonfioSdk\Services\DocumentsService;
use KonfioSdk\Services\AwsService;
use KonfioSdk\Test\Mock\AwsSdkMock;

class KonfioServiceAdapter
{
    // const AWS_CLASS = \KonfioSdk\Test\Mock\AwsSdkMock::class;
    const AWS_CLASS = \Aws\Sdk::class;

    const DOCUMENTS_SERVICE = 'documents';
    const AWS_SERVICE = 'aws';

    public static function getSdkServiceProvider()
    {
        $sp = new ServiceProvider;

        return $sp;
    }

    public static function getSdkServiceConfig()
    {
        $sp = self::getSdkServiceProvider();

        return $sp->getConfig();
    }

    public static function get(String $service)
    {
        if ($service == self::DOCUMENTS_SERVICE) {
            return self::getDocumentsService();
        }

        if ($service == self::AWS_SERVICE) {
            return self::getAwsService();
        }
    }

    private static function getAwsService(): AwsService
    {
        $sp = self::getSdkServiceProvider();

        $config = self::getSdkServiceConfig();

        $awsService = $sp->getService(self::AWS_SERVICE);

        return $awsService;
    }

    private static function getDocumentsService(): DocumentsService
    {
        $sp = self::getSdkServiceProvider();

        $documentsService = $sp->getService(self::DOCUMENTS_SERVICE);

        $config = self::getSdkServiceConfig();

        $awsService = self::get(self::AWS_SERVICE);

        $awsService->setSdkConfig($config['aws'])
                    ->setSdk(self::AWS_CLASS);

        $env = getenv('ENV');

        if ($env == 'dev' || $env == 'local') {
            $documentsService->setProcessingBucket('processdocumentsdev');
            $documentsService->setUploadBucket('clientsbucket-backup-dev');
            $documentsService->setMergePdfsBucket('konfiobucket-dev');
        }

        return $documentsService;
    }
}
