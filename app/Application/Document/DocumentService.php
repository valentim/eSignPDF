<?php

namespace App\Application\Document;

use App\Infrastructure\Services\AwsS3Service;
use App\Infrastructure\Services\EIDEasyService;
use App\Domain\Document\DocumentRepository;
use App\Domain\Document\Document;
use App\Infrastructure\Services\DocumentType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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

    public function getDocuments($userId)
    {
        return $this->documentRepository->getByUserId($userId);
    }

    public function downloadSignedFile(Document $document)
    {
        try {
            $file = $this->eIDEasyService->downloadSignedFile($document);
        } catch (\Exception $e) {
            Log::error("Failed to download signed file from eID Easy", ['document' => $document, 'error' => $e->getMessage()]);
            throw new \Exception("File download failed");
        }

        if (!$this->s3Service->uploadFile("/documents/{$document->signed_filename}", $file)) {
            Log::error("Failed to upload signed file to S3");
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
                Log::error("Failed to upload signed file to S3", ['document' => $document]);
                throw new \Exception("File upload failed");
            }

            $originalFileUpdatedAt = Carbon::now();
        }

        try {
            [$doc_id, $signing_page_url] = $this->eIDEasyService->prepareFilesForSigning($fileContent, $document);

            $document = $this->documentRepository->update($document, [
                'doc_id' => $doc_id,
                'signing_page_url' => $signing_page_url,
                'original_file_upload_at' => $originalFileUpdatedAt
            ]);

            return $document;
        } catch (\Exception $e) {
            Log::error("Failed to prepare files for signing", ['document' => $document, 'error' => $e->getMessage()]);
            throw new \Exception("File signing failed");
        }
    }

    public function deleteFiles(Document $document)
    {
        DB::beginTransaction();
        try {
            $this->s3Service->deleteFile($document, DocumentType::Original);
            $this->s3Service->deleteFile($document, DocumentType::Signed);
            $this->documentRepository->delete($document);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete files", ['document' => $document]);
            throw new \Exception("File deletion failed");
        }
    }

    public function getOriginalFile(Document $document)
    {
        try {
            return $this->s3Service->downloadFile($document, DocumentType::Original);
        } catch (\Exception $e) {
            Log::error("Failed to download original file from S3", ['document' => $document, 'type' => DocumentType::Original]);
            throw new \Exception("File download failed");
        }
    }

    public function getTemporaryDownloadUrl(Document $document, DocumentType $type, $expiration = 60)
    {
        try {
            return $this->s3Service->getTemporaryUrl($document, $type, Carbon::now()->addMinutes($expiration));
        } catch (\Exception $e) {
            Log::error("Failed to get temporary download URL", ['document' => $document, 'type' => $type, 'expiration' => $expiration, 'error' => $e->getMessage()]);
            throw new \Exception("Temporary URL generation failed");
        }
    }
}
