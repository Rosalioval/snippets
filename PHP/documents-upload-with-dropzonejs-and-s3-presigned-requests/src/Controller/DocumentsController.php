<?php

namespace Solicitud\Controller;

use Application\Form\DocumentsForm;
use Utils\Dev\PdfHelper;
use Zend\File\Transfer\Adapter\Http;
use Aws\S3\Exception\S3Exception;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Aws\S3\S3Client;
use KonfioSdk\Application\Type as ApplicationType;
use KonfioSdk\Kyc\KycClient;
use KonfioSdk\Models\UserDocuments;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use KonfioSdk\Services\ServiceProvider;
use KonfioSdk\Services\DocumentsService;
use KonfioSdk\Test\Mock\AwsSdkMock;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use KonfioSdk\Exceptions\EntityNotFoundException;
use KonfioSdk\Exceptions\RegisteringDocumentException;
use KonfioSdk\User\User;
use UIV2\Service\UserTrackingService;

class DocumentsController extends SolicitudController
{
    const STATUS_PENDING = 'P';
    const STATUS_DENIED = 'D';
    const STATUS_APPROVED = 'A';

    /**
     * These constants define the document keys to display the document examples in the UI
     * the doc_ids match the LU_AWS_S3_FILE_TYPE docs
     */
    const CFE_INVOICE = 17;
    const OFFICIAL_ID = 23;
    const RFC = 49;
    const CURP = 53;
    const PROOF_OF_ADDRESS = 8;

    // const AWS_CLASS = \KonfioSdk\Test\Mock\AwsSdkMock::class;
    const AWS_CLASS = \Aws\Sdk::class; // PROD

    public function subirAction()
    {
        $user = $this->getUser();

        $userId = $user->user_id;

        $request = $this->getRequest();

        $response = $this->getResponse();

        $sp = new ServiceProvider;

        $config = $sp->getConfig();

        $documentsService = $sp->getService('documents');

        $awsService = $sp->getService('aws')
                        ->setSdkConfig($config['aws'])
                        ->setSdk(self::AWS_CLASS);

        if ($env == 'dev' || $env == 'local') {
            $documentsService->setProcessingBucket('processdocumentsdev');
            $documentsService->setUploadBucket('clientsbucket-backup-dev');
            $documentsService->setMergePdfsBucket('konfiobucket-dev');
        }

        if ($request->isPost()) {
            $files = $request->getFiles()->toArray();

            $files = reset($files);

            $data = $request->getPost();

            $docId = $data['documentId'];

            $docCount = count($files);

            try {
                $endpoints = $documentsService->getDocumentUploadUrls($userId, $docId, $docCount);

                $uploadInfo = $this->uploadDocuments($endpoints, $files);

                $result = $this->registerDocumentsUpload($sp, $user, $docId, $docCount);

                $filename = $documentsService->getFilename($docId);

                $eventName = "server-uploaded-$docId-$filename";
                UserTrackingService::sendMPEvent($eventName, $userId, [
                    'documentCount' => $docCount,
                    'documentId' => $docId,
                    'filename' => $filename,
                    ]);

            } catch (ModelNotFoundException $e) {
                return $this->setResponse($e, 505);
            } catch (RegisteringDocumentException $e) {
                return $this->setResponse($e, 505);
            } catch (EntityNotFoundException $e) {
                return $this->setResponse($e, 505);
            } catch (\Exception $e) {
                return $this->setResponse($e, 505);
            }

            $res = [
                'files' => $files,
                'data' => $data,
                'endpoints' => $endpoints,
                'result' => [],
                'uploadInfo' => $uploadInfo,
            ];

            $response
                ->setStatusCode(200)
                ->setContent($res);

            return new JsonModel($res);
        }

        $verificationDocsConfig = $config['lambda']['set_verification_docs'];

        $documentsService->setVerificationDocsConfig($verificationDocsConfig);

        $documents = $documentsService->getUserRequiredDocuments($user)['requiredDocs'];

        $this->layout('solicitud/layout');

        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

        $isAndroid = (stripos($userAgent, 'android') !== false);

        UserTrackingService::sendMPEvent('server-entered-documentos', $userId, []);

        return new ViewModel([
            'isAndroid' => $isAndroid,
            'documents' => $documents,
            'getState' => array(self::class, 'getDocumentState'),
            'getExample' => array(self::class, 'getDocumentExample'),
        ]);
    }

    public function getUserRequiredDocumentsAction()
    {
        $user = $this->getUser();

        $userId = $user->user_id;

        $sp = new ServiceProvider;

        $env = getenv('ENV');

        $sp->setConfigPath(getcwd() . "/config/autoload/serviceprovider.$env.json");

        $config = $sp->getConfig();

        $documentsService = $sp->getService('documents');

        $awsService = $sp->getService('aws')
                        ->setSdkConfig($config['aws'])
                        ->setSdk(self::AWS_CLASS);

        $verificationDocsConfig = $config['lambda']['set_verification_docs'];

        $documentsService->setVerificationDocsConfig($verificationDocsConfig);

        $documents = $documentsService->getUserRequiredDocuments($user)['requiredDocs'];

        $allDocsAreUploaded = count(array_filter($documents, function($doc){
            return $doc['status'] == null;
        })) == 0;

        $data = [
            'documents' => $documents,
            'complete' => false,
        ];

        if ($allDocsAreUploaded) {
            $data['complete'] = true;

            UserTrackingService::sendMPEvent('server-completed-documents', $userId, []);
        }

        return new JsonModel($data);
    }

    private function setResponse($exception, $code)
    {
        $error = $exception->getMessage();
        $message = 'No hemos podido guardar tus documentos, por favor intenta de nuevo';

        error_log($error);

        $this->getResponse()
            ->setStatusCode($code)
            ->setContent($error);

        return new JsonModel(['error' => $message]);
    }

    public function uploadDocuments(Array $endpoints, Array $files)
    {
        $info = [];

        foreach ($files as $index => $file) {
            $url = $endpoints[$index]['url'];

            $localFile = $file['tmp_name'];

            $fp = fopen($localFile, 'r');

            $size = (string) filesize($localFile);

            $type = $file['type'];

            $headers = [
                "Content-Length: $size",
                "Content-Type: $type"
            ];

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_INFILE, $fp);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
            curl_setopt($ch, CURLOPT_UPLOAD, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 86400);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFile));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }

            curl_close($ch);

            $info[] = [
                'headers' => $headers,
                'response' => $response,
                'object' => gettype($file),
            ];
        }

        return $info;
    }

    public function registerDocumentsUpload(
        ServiceProvider $sp,
        User $user,
        Int $docId,
        Int $count)
    {
        $config = $sp->getConfig();

        $documentsService = $sp->getService('documents');

        $imagesToPdfConfig = $config['lambda']['imagesToPdf'];

        $documentsService->setImgToPdfConfig($imagesToPdfConfig);

        $mergePdfsConfig = $config['lambda']['mergePdfs'];

        $documentsService->setMergePdfsLambdaConfig($mergePdfsConfig);

        $result = $documentsService->registerDocumentUpload($user, $docId, $count);

        return $result;
    }

    /**
     * Returns a string that defines the state of the document in the UI
     *
     * @param Array $document
     *
     * @return String $state
     **/
    static function getDocumentState(Array $document = []): string
    {
        $status = $document['status'];

        if ($status == self::STATUS_PENDING) {
            return 'is-pending';
        }

        if ($status == self::STATUS_APPROVED) {
            return 'is-approved';
        }

        if ($status == self::STATUS_DENIED) {
            return 'is-denied';
        }

        return '';
    }

    /**
     * Returns a string that defines the img path of the file example
     *
     * @param Array $document
     *
     * @return String $imgPath
     **/
    static function getDocumentExample(Array $document = []): string
    {
        $docId = $document['key'];

        if ($docId == self::CURP) {
            return '/img/docs/docs-ejemplo-curp.png';
        }

        if ($docId == self::OFFICIAL_ID) {
            return '/img/docs/docs-ejemplo-ife.png';
        }

        if ($docId == self::RFC) {
            return '/img/docs/docs-ejemplo-rfc.png';
        }

        if ($docId == self::CFE_INVOICE ||
            $docId == self::PROOF_OF_ADDRESS) {
            return '/img/docs/docs-ejemplo-recibo-de-luz.png';
        }

        return '';
    }
}
