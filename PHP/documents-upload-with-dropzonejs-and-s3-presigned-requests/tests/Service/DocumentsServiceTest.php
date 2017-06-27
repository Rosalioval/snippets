<?php

namespace KonfioSdk\Services;

use PHPUnit\Framework\TestCase;
use KonfioSdk\User\User;
use KonfioSdk\Migrations\User as UserMigration;
use KonfioSdk\Migrations\LuAwsS3FileType as LuAwsS3FileTypeMigration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use KonfioSdk\Test\Mock\AwsSdkMock;
use KonfioSdk\Exceptions\EntityNotFoundException;
use KonfioSdk\Exceptions\RegisteringDocumentException;
use KonfioSdk\Models\AwsFileOnRecord;

class DocumentsServiceTest extends TestCase
{
    protected $user = null;

    const DOC_ID = 36; // IFE

    protected $documentsService = null;

    protected $sp = null;

    public function setUp()
    {
        UserMigration::reset();

        putenv('ENV=dev');

        $user = new User;
        $user->user_id = 266410;
        $user->email = 'email@email.com';
        $user->password = 'xxxx';
        $user->CURP = 'GUSP850424HASTRP05';
        $user->save();

        $user->createNewApplication();

        $this->user = User::find($user->user_id);

        $this->sp = new ServiceProvider;

        $env = getenv('ENV');

        $this->sp->setConfigPath(dirname(__DIR__, 1) . "/config/serviceprovider.{$env}.json");

        $this->documentsService = $this->sp->getService('documents');
    }

    public function testGetDocumentUploadURLs()
    {
        $message = 'Test that the ServiceProvider calls a DocumentsService getDocumentUploadUrls method that uses an AwsService s3Client';

        $processingBucket = 'processdocumentsdev';

        $this->documentsService->setProcessingBucket($processingBucket);

        $this->sp->getService('aws')->setSdk(AwsSdkMock::class);

        $userId = $this->user->user_id;

        $documentId = self::DOC_ID;

        $count = 3;

        $S3ClientUrls = $this->documentsService->getDocumentUploadUrls($userId, $documentId, $count);

        $reference = $S3ClientUrls[$count-1]['reference'];
        $reference = explode('/', $reference);

        $bucket = $this->documentsService->getProcessingBucket();

        $this->assertEquals($processingBucket, $bucket, $message);
        $this->assertEquals($bucket, $reference[0], $message);
        $this->assertEquals($userId, $reference[1], $message);
        $this->assertEquals($documentId, $reference[2], $message);
        $this->assertEquals($count, $reference[3], $message);
    }

    public function testDocumentNotFoundException()
    {
        $message = 'Test that the DocumentsService returns a ModelNotFoundException';

        $userId = $this->user->user_id;

        $documentId = 365;

        $this->expectException(ModelNotFoundException::class, $message);

        $S3ClientUrls = $this->documentsService->getDocumentUploadUrls($userId, $documentId);
    }

    public function testMergeImagesToPdf()
    {
        $message = 'Test that the DocumentsService mergeImagesToPdf function returns an Array result from a imagesToPdfs lambda';

        $this->documentsService->setProcessingBucket('processdocumentsdev');

        $this->documentsService->setUploadBucket('clientsbucket-backup-dev');

        $config = $this->sp->getConfig();

        $this->sp->getService('aws')
            ->setSdkConfig($config['aws'])
            ->setSdk(AwsSdkMock::class);

        $this->documentsService->setImgToPdfConfig($config['lambda']['imagesToPdf']);

        $documentId = self::DOC_ID;

        $mergedImages = $this->documentsService->mergeImagesToPdf($this->user, $documentId, 1);

        $this->assertEquals('clientsbucket-backup-dev', $mergedImages['Bucket'], $message);
        $this->assertEquals('CURP123456TEST/verification/ife.pdf', $mergedImages['Key'], $message);
    }

    public function testGetUserRequiredDocs()
    {
        $message = 'Test that the DocumentsService getUserRequiredDocuments returns a correctly formatted array';

        $config = $this->sp->getConfig();

        $verificationDocsConfig = $config['lambda']['set_verification_docs'];

        $this->documentsService->setVerificationDocsConfig($verificationDocsConfig);

        $this->sp->getService('aws')
            ->setSdkConfig($config['aws'])
            ->setSdk(AwsSdkMock::class);

        $requiredDocs = $this->documentsService->getUserRequiredDocuments($this->user);

        $this->assertArrayHasKey('requiredDocs', $requiredDocs);

        $docFields = $requiredDocs['requiredDocs'][0];

        $this->assertArrayHasKey('status', $docFields, $message);
        $this->assertArrayHasKey('approvedDate', $docFields, $message);
        $this->assertArrayHasKey('key', $docFields, $message);
        $this->assertArrayHasKey('fileDescription', $docFields, $message);
        $this->assertArrayHasKey('uploadedDate', $docFields, $message);
        $this->assertArrayHasKey('label', $docFields, $message);
        $this->assertArrayHasKey('rejectionReason', $docFields, $message);
        $this->assertArrayHasKey('filename', $docFields, $message);
    }

    public function testGetFilename()
    {
        $sp = new ServiceProvider;

        $documentsService = $this->sp->getService('documents');

        $docId = 345;

        $message = 'Test that the DocumentsService getFilename returns an exception on document not found';
        $this->expectException(EntityNotFoundException::class, $message);

        $filename = $this->documentsService->getFilename($docId);

        $docId = self::DOC_ID;

        $filename = $this->documentsService->getFilename($docId);

        $message = 'Test that the DocumentsService getFilename returns a filename string';
        $this->assertEquals('ife', $filename, $message);
    }

    public function testMergePdfs()
    {
        $config = $this->sp->getConfig();

        $this->documentsService->setProcessingBucket('processdocumentsdev');

        $this->documentsService->setUploadBucket('clientsbucket-backup-dev');

        $imagesToPdfConfig = $config['lambda']['imagesToPdf'];

        $this->documentsService->setImgToPdfConfig($imagesToPdfConfig);

        $mergePdfsConfig = $config['lambda']['mergePdfs'];

        $this->documentsService->setMergePdfsLambdaConfig($mergePdfsConfig);

        $this->documentsService->setMergePdfsBucket('konfiobucket-dev');

        $this->sp->getService('aws')
            ->setSdkConfig($config['aws'])
            ->setSdk(AwsSdkMock::class);

        $docId = self::DOC_ID;

        $filesPostMerge = $this->documentsService->mergeImagesToPdf($this->user, $docId, 1);

        $filename = $this->documentsService->getFilename($docId);

        $result = $this->documentsService->mergePdfs($this->user, $filename, $filesPostMerge);

        $message = 'Test that the DocumentsService mergePdfs method returns a result array from the lambda client after the pdfs where merged';

        $this->assertArrayHasKey('Bucket', $result, $message);
        $this->assertArrayHasKey('Key', $result, $message);
        $this->assertArrayHasKey('ETag', $result, $message);
        $this->assertArrayHasKey('VersionId', $result, $message);
    }

    public function testRegisterDocumentUploadThrowsException()
    {
        $config = $this->sp->getConfig();

        $this->documentsService->setProcessingBucket('processdocumentsdev');

        $this->documentsService->setUploadBucket('clientsbucket-backup-dev');

        $imagesToPdfConfig = $config['lambda']['imagesToPdf'];

        $this->documentsService->setImgToPdfConfig($imagesToPdfConfig);

        $mergePdfsConfig = $config['lambda']['error'];

        $this->documentsService->setMergePdfsLambdaConfig($mergePdfsConfig);

        $this->documentsService->setMergePdfsBucket('konfiobucket-dev');

        $this->sp->getService('aws')
            ->setSdkConfig($config['aws'])
            ->setSdk(AwsSdkMock::class);

        $docId = self::DOC_ID;

        $message = 'Test that the DocumentsService registerDocumentUpload method throws a RegisteringDocumentException on S3 result error';

        $this->expectException(RegisteringDocumentException::class, $message);

        $this->documentsService->registerDocumentUpload($this->user, $docId, 1);
    }

    public function testAwsFileOnRecordHasTheConvertedFilesInfo()
    {
        $config = $this->sp->getConfig();

        $this->documentsService->setProcessingBucket('processdocumentsdev');

        $this->documentsService->setUploadBucket('clientsbucket-backup-dev');

        $imagesToPdfConfig = $config['lambda']['imagesToPdf'];

        $this->documentsService->setImgToPdfConfig($imagesToPdfConfig);

        $mergePdfsConfig = $config['lambda']['mergePdfs'];

        $this->documentsService->setMergePdfsLambdaConfig($mergePdfsConfig);

        $this->documentsService->setMergePdfsBucket('konfiobucket-dev');

        $this->sp->getService('aws')
            ->setSdkConfig($config['aws'])
            ->setSdk(AwsSdkMock::class);

        $docId = self::DOC_ID;

        $message = 'Test that the DocumentsService registerDocumentUpload method returns a result';

        $this->documentsService->setMergePdfsLambdaConfig($mergePdfsConfig);

        $results = $this->documentsService->registerDocumentUpload($this->user, $docId, 1);

        $bucket = $results['Bucket'];

        $key = $results['Key'];

        $versionId = $results['VersionId'];

        $s3Path = "$key";

        $version = 1;

        $awsFileOnRecord = AwsFileOnRecord::where('CURP', $this->user->CURP)->first();

        $this->assertEquals($s3Path, $awsFileOnRecord->S3_PATH);
        $this->assertEquals($version, $awsFileOnRecord->VERSION);
        $this->assertEquals($versionId, $awsFileOnRecord->AWS_VERSION_ID);
        $this->assertEquals($this->user->CURP, $awsFileOnRecord->CURP);
        $this->assertEquals($docId, $awsFileOnRecord->S3_FILE_TYPE);
        $this->assertEquals(date('Y-m-d'), $awsFileOnRecord->UPLOAD_DATE);
        $this->assertEquals('P', $awsFileOnRecord->IS_VALID);
    }
}
