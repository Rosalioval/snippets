<?php

namespace KonfioSdk\Services;

use KonfioSdk\Models\LookupTables;
use KonfioSdk\Models\LuAwsS3FileType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use KonfioSdk\User\User;
use KonfioSdk\Exceptions\EntityNotFoundException;
use KonfioSdk\Exceptions\RegisteringDocumentException;
use KonfioSdk\Models\UserDocuments;

class DocumentsService extends ServiceProvider
{
    /**
     * @var String LU_FILE_TYPES The lookup table where the document types are listed
     */
    const LU_FILE_TYPES = 'LU_AWS_S3_FILE_TYPE';

    /**
     * @var String $processingBucket The default S3 bucket where the raw files
     *                               are uploaded before PDF conversion
     */
    protected $processingBucket = 'processdocuments';

    /**
     * @var String $uploadPdfBucket The default S3 bucket where the merged pdf files are uploaded
     */
    protected $uploadPdfBucket = 'clientsbucket-backup';

    /**
     * @var Array $mergePdfsBucket the bucket name to store the merged pdfs file
     */
    protected $mergePdfsBucket = 'clientsbucket';

    /**
     * @var String $expire Determines the expiration time of a Aws Guzzle\PSR7\Request
     */
    protected $expire = '+20 minutes';

    /**
     * @var Array $imgToPdfConfig Property can be set and get for the mergeimgsToPdf method
     */
    protected $imgToPdfConfig = [];

    /**
     * @var Array $setVerificationDocsConfig the config for the set_verification_docs_DEV lambda func
     */
    protected $verificationDocsConfig = [];

    /**
     * @var Array $mergePdfsLambdaConfig the config for the mergePDfs lambda func
     */
    protected $mergePdfsLambdaConfig = [];


    /**
     * property getters and setters
     **/
    public function getMergePdfsBucket(): String
    {
        return $this->mergePdfsBucket;
    }

    public function setMergePdfsBucket(String $bucket)
    {
        $this->mergePdfsBucket = $bucket;

        return $this;
    }

    public function getMergePdfsLambdaConfig(): Array
    {
        return $this->mergePdfsLambdaConfig;
    }

    public function setMergePdfsLambdaConfig(Array $config)
    {
        $this->mergePdfsLambdaConfig = $config;

        return $this;
    }

    public function getVerificationDocsConfig(): Array
    {
        return $this->verificationDocsConfig;
    }

    public function setVerificationDocsConfig(Array $config)
    {
        $this->verificationDocsConfig = $config;

        return $this;
    }

    public function setProcessingBucket(String $bucketName)
    {
        $this->processingBucket = $bucketName;

        return $this;
    }

    public function getProcessingBucket(): String
    {
        return $this->processingBucket;
    }

    public function setRequestExpiration(String $time)
    {
        $this->expire = $time;
    }

    public function getRequestExpiration(): String
    {
        return $this->expire;
    }

    public function setUploadBucket(String $bucketName)
    {
        $this->uploadPdfBucket = $bucketName;

        return $this;
    }

    public function getUploadBucket(): String
    {
        return $this->uploadPdfBucket;
    }

    public function setImgToPdfConfig(Array $config)
    {
        $this->imgToPdfConfig = $config;

        return $this;
    }

    public function getImgToPdfConfig(): Array
    {
        return $this->imgToPdfConfig;
    }

    /**
     *  getDocumentUploadUrls
     *
     *  creates a list of url so you can upload multiple files per
     *  document. After uploading the files it is requires for you
     *  to confirm the uploads.
     *
     *  @param Int userId the user id
     *  @param Int $documentId the document id
     *  @param Int $count the amount of files you want to upload for this document
     *
     *  @return Array list of URLs to use with PUT request to upload files to s3.
     */
    public function getDocumentUploadUrls(Int $userId, Int $documentId, Int $count = 1): Array
    {
        $luTables = new LookupTables;

        $fileTypesTable = $luTables->getLuTable(self::LU_FILE_TYPES);

        $fileInfo = $fileTypesTable->where('FILE_TYPE_ID', $documentId)->first();

        if (!$fileInfo) {
            throw new ModelNotFoundException("Document FILE_TYPE_ID does not exist");
        }

        $awsService = $this->getService('aws');

        $s3Client = $awsService->getSdk()->createS3();

        $urls = array_fill(1, $count, null);

        $bucket = $this->getProcessingBucket();

        $result = [];

        $expire = $this->getRequestExpiration();

        for ($i = 0; $i < $count; $i++) {
            $fileCount = $i + 1;
            $key = "{$userId}/{$documentId}/{$fileCount}";

            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key'    => $key
            ]);


            $request = $s3Client->createPresignedRequest($cmd, $expire);

            $result[] = [
                'url' => (string) $request->getUri(),
                'reference' => "{$bucket}/{$key}"
            ];
        }

        return $result;
    }

    /**
     *  registerDocumentUpload
     *
     *  Confirms a documents upload by specifying how many documents were uploaded
     *  for said document.
     *
     *  @param Int userId the user id
     *  @param Int $documentId the document name
     *  @param Int $count the amount of files uploaded
     *
     *  @return Array That includes the aws returned information of where the document was uploaded to in S3.
     * */
    public function registerDocumentUpload(User $user, $documentId, $count)
    {
        $filesPostMerge = $this->mergeImagesToPdf($user, $documentId, $count);

        $filename = $this->getFilename($documentId);

        $result = $this->mergePdfs($user, $filename, $filesPostMerge);

        if (key_exists('errorMessage', $result)) {
            throw new RegisteringDocumentException($result['errorMessage']);
        }

        $this->insertAwsFileOnRecord($result, $user, $documentId);

        return $result;
    }

    /**
     * InsertAwsFileOnRecord prepare and insert the information in AWS_FILE_ON_RECORD table.
     *
     * @param Array $result the response of **mergePdfs** method. Should include keys VersionId, Bucket and Key. Version key is optional.
     * @param Object User\User
     * @param Integer $documentId the id of the document.
     */
    private function insertAwsFileOnRecord(Array $result, User $user, Int $documentId)
    {
        $bucket = $result['Bucket'];

        $key = $result['Key'];

        $s3Path = "{$key}";

        $curp = $user->CURP;

        $awsVersion = $result['VersionId'];

        $version = isset($result['Version']) ? $result['Version'] : 1;

        $userDocs = new UserDocuments;

        $userDocs->insertAwsFileOnRecord($s3Path, $version, $awsVersion, $curp, $documentId, 'P', null);

        return $this;
    }

    /**
     *  mergePdfs
     *
     *  Calls the lambda service that merges all pdfs into a single pdf and then
     *  puts that pdf in S3
     *
     *  @param Object User\User
     *  @param String $documentName the document name
     *  @param Array of s3 keys of all the pdfs to merge into one
     *
     *  @return Array that includes where the files was uploaded and the Etag
     */
    public function mergePdfs(User $user, String $documentName, Array $files = [])
    {
        $userId = $user->user_id;

        $aws = $this->getService('aws')->getSdk();

        $lambdaClient = $aws->createLambda();

        $mergePdfConfig = $this->getMergePdfsLambdaConfig();

        $mergePdfConfig['Qualifier'] = $mergePdfConfig['version'];

        $mergePdfConfig['FunctionName'] = $mergePdfConfig['name'];

        $mergePdfConfig['ContentType'] = 'application/pdf';

        $bucket = $this->getMergePdfsBucket();

        $CURP = $user->CURP;

        $payload = [
            'upload' => [
                'Bucket' => $bucket,
                'Key' => "{$CURP}/verification/{$documentName}.pdf"
            ],
            'files' => $files
        ];

        $mergePdfConfig['Payload'] = json_encode($payload);

        $result = $lambdaClient->invoke($mergePdfConfig);

        $lambdaResult = json_decode($result->get('Payload')->getContents(), true);

        $finalPdfLocation = $payload['upload'];

        return array_merge($finalPdfLocation, $lambdaResult);
    }

    /**
     * GetFilename get the filename template for the given document type
     *
     * @param Integer $docId the id of the document
     * @throws ModelNotFoundException if the file type id for the document does not exist.
     * @return Integer the file type id.
     */
    public function getFilename(Int $docId)
    {
        $luTables = new LookupTables;

        $fileTypesTable = $luTables->getLuTable(self::LU_FILE_TYPES);

        $fileInfo = $fileTypesTable
                    ->where('FILE_TYPE_ID', $docId)
                    ->first();

        if ($fileInfo == null) {
            throw new EntityNotFoundException("File info does not exist for this document ID [$docId]");
        }

        $fileNameTemplate = $fileInfo->FILE_NAME_TEMPLATE;

        $pos = strrpos($fileNameTemplate, "_");

        $filename = substr($fileNameTemplate, 0, $pos);

        return $filename;
    }

    /**
     *  mergeImagesToPdf
     *
     *  Calls the lambda service that merges all images into a single pdf and then
     *  puts that pdf in S3
     *
     *  @param User\User
     *  @param Int $documentId the document name
     *  @param Int $count the amount of files uploaded
     *
     *  @return Array of keys in S3 of the resulting files
     */
    public function mergeImagesToPdf(User $user, $documentId, $count)
    {
        $aws = $this->getService('aws')->getSdk();

        $lambdaClient = $aws->createLambda();

        $userId = $user->user_id;

        $userCurp = $user->CURP;

        $doc = LuAwsS3FileType::findOrFail($documentId);

        $docNameTemplate = $doc->FILE_NAME_TEMPLATE;

        $docName = $this->replaceFileExtension($docNameTemplate, "pdf");

        $amountOfDocs = array_fill(1, $count, null);

        $bucket = $this->getProcessingBucket();

        $docsToRegister = array_map(function($docCount)
            use ($userId, $documentId, $bucket) {
                return [
                    'Bucket' => $bucket,
                    'Key' => "{$userId}/{$documentId}/{$docCount}"
                ];
        }, array_keys($amountOfDocs));

        $imageToPdfConfig = $this->getImgToPdfConfig();

        $imageToPdfConfig['Qualifier'] = $imageToPdfConfig['version'];

        $imageToPdfConfig['FunctionName'] = $imageToPdfConfig['name'];

        $uploadBucket = $this->getUploadBucket();

        $payload = [
            'upload' => [
                'Bucket' => $uploadBucket,
                'Key' => "{$userCurp}/verification/{$docName}"
            ],
            'files' => $docsToRegister
        ];

        $payload = json_encode($payload);

        $imageToPdfConfig['Payload'] = $payload;

        $result = $lambdaClient->invoke($imageToPdfConfig);

        return json_decode($result->get('Payload')->getContents(), true);
    }

    /**
     *  getUserRequiredDocuments
     *
     *  Calls the set_verification_docs_ENV lambda function that gets the USER_REQ_VERIFICATION_DOCS
     *  and the AWS_FILES_ON_RECORD of the given user
     *
     *  @param User\User
     *  @param Bool $updateUserDocs determines whether if the lambda needs to update the USER_REQ_VERIFICATION_DOCS records
     *
     *  @return Array of keys in S3 of the resulting files
     */
    public function getUserRequiredDocuments(User $user, Bool $updateUserDocs = false)
    {
        $aws = $this->getService('aws')->getSdk();

        $lambdaClient = $aws->createLambda();

        $userId = $user->user_id;

        $appId = $user->getLatestApp()->APPLICATION_ANSWERS_ID;

        $config = $this->getVerificationDocsConfig();

        $config['Qualifier'] = $config['version'];

        $config['FunctionName'] = $config['name'];

        $payload = [
            'applicationId' => $appId,
            'create' => $updateUserDocs,
        ];

        $payload = json_encode($payload);

        $config['Payload'] = $payload;

        $result = $lambdaClient->invoke($config);

        return json_decode($result->get('Payload')->getContents(), true);
    }

    /**
     * ReplaceFileExtension takes the LU_AWS_FILE_TYPE.FILE_NAME_TEMPLATE value and appends it the file anme estension.
     *
     * @param string $template the template file name
     * @param string $extension the filenma estension to use
     *
     * @return string the filename with extension
     */
    private function replaceFileExtension(String $template, String $extension): String
    {
        $idx = strripos($template, "_");

        $filename = substr($template, 0, $idx);

        return "{$filename}.{$extension}";
    }
}
