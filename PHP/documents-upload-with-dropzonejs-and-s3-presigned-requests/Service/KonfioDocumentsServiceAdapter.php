<?php

namespace Solicitud\Service;

use KonfioSdk\User\User;

class KonfioDocumentsServiceAdapter extends KonfioServiceAdapter
{
    public static function getUserRequiredDocuments(User $user)
    {
        $documentsService = self::get(self::DOCUMENTS_SERVICE);

        $config = self::getSdkServiceConfig();

        $verificationDocsConfig = $config['lambda']['set_verification_docs'];

        $documentsService->setVerificationDocsConfig($verificationDocsConfig);

        $requiredDocs = $documentsService->getUserRequiredDocuments($user)['requiredDocs'];

        return $requiredDocs;
    }

    public static function getDocumentUploadUrls(Int $userId, Int $documentId, Int $count = 1): Array
    {
        $documentsService = self::get(self::DOCUMENTS_SERVICE);

        return $documentsService->getDocumentUploadUrls($userId, $documentId, $count);
    }

    public static function getFilename(Int $docId)
    {
        $documentsService = self::get(self::DOCUMENTS_SERVICE);

        return $documentsService->getFilename($docId);
    }

    public static function registerDocumentsUpload(
        User $user,
        Int $docId,
        Int $count)
    {
        $documentsService = self::get(self::DOCUMENTS_SERVICE);

        $config = self::getSdkServiceConfig();

        $imagesToPdfConfig = $config['lambda']['imagesToPdf'];

        $documentsService->setImgToPdfConfig($imagesToPdfConfig);

        $mergePdfsConfig = $config['lambda']['mergePdfs'];

        $documentsService->setMergePdfsLambdaConfig($mergePdfsConfig);

        $result = $documentsService->registerDocumentUpload($user, $docId, $count);

        return $result;
    }
}
