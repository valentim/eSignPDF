<?php

namespace App\Application\Document;

use App\Infrastructure\Services\AwsS3Service;
use App\Infrastructure\Services\EIDEasyService;
use App\Domain\Document\DocumentRepository;
use App\Domain\Document\Document;
use App\Infrastructure\Services\DocumentType;
use Carbon\Carbon;

class DocumentService
{
    protected $s3Service;
    protected $documentRepository;

    public function __construct(EIDEasyService $eIDEasyService, AwsS3Service $s3Service, DocumentRepository $documentRepository)
    {
    	$this->eIDEasyService = $eIDEasyService;
        $this->s3Service = $s3Service;
        $this->documentRepository = $documentRepository;
    }

    public function downloadSignedFile(Document $document)
    {
        $file = $this->eIDEasyService->downloadSignedFile($document);
        if (!$this->s3Service->uploadFile("/documents/{$document->filename}", $file)) {
            throw new \Exception("File upload failed");
        }

        $this->documentRepository->update($document, [
            'signed_at' => Carbon::now(),
            'signed_file_upload_at' => Carbon::now()
        ]);
    }

    
    public function signFile($file, $userId, ?Document $document = null) : Document
    {
        $fileContent = $file;

        $originalFileUpdatedAt = $document->original_file_upload_at ?? null;
        if ($document === null) {
            $filename = $file->getClientOriginalName();
            $fileContent = file_get_contents($fileContent);
            $time = time();
            $document = $this->documentRepository->create([
                'filename' => $filename,
                'user_id' => $userId,
                'signed_filename' => "{$time}_signed_{$filename}"
            ]);

            if (!$this->s3Service->uploadFile("/documents/{$document->uuid}_{$document->filename}", $fileContent)) {
                throw new \Exception("File upload failed");
            }

            $originalFileUpdatedAt = Carbon::now();
        }

        [$doc_id, $signing_page_url] = $this->eIDEasyService->prepareFilesForSigning($fileContent, $document);

        $document = $this->documentRepository->update($document, [
            'doc_id' => $doc_id,
            'signing_page_url' => $signing_page_url,
            'original_file_upload_at' => $originalFileUpdatedAt
        ]);

        return $document;
    }

    public function deleteFiles(Document $document)
    {
        $this->s3Service->deleteFile($document, DocumentType::Original);
        $this->s3Service->deleteFile($document, DocumentType::Signed);
        $this->documentRepository->delete($document);
    }

    public function getOriginalFile(Document $document)
    {
        return $this->s3Service->downloadFile($document, DocumentType::Original);
    }

    public function getTemporaryDownloadUrl(Document $document, DocumentType $type, $expiration = 60)
    {
        return $this->s3Service->getTemporaryUrl($document, $type, Carbon::now()->addMinutes($expiration));
    }
}
