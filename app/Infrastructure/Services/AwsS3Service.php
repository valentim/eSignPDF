<?php 
namespace App\Infrastructure\Services;

use Illuminate\Support\Facades\Storage;
use App\Domain\Document\Document;
use Illuminate\Support\Facades\Log;

enum DocumentType: string {
    case Original = 'original';
    case Signed = 'signed';
}

class AwsS3Service
{
    public function uploadFile($filePath, $fileContent)
    {
        return Storage::disk('s3')->put($filePath, $fileContent);
    }

    public function downloadFile(Document $document, DocumentType $type)
    {
        $documentName = "{$document->uuid}_{$document->filename}";
        if ($type == DocumentType::Signed) {
            $documentName = $document->signed_filename;
        }

        $documentPath = "/documents/{$documentName}";

        return Storage::disk('s3')->get($documentPath);
    }

    public function deleteFile(Document $document, DocumentType $type)
    {
        $documentName = "{$document->uuid}_{$document->filename}";
        if ($type == DocumentType::Signed) {
            $documentName = $document->signed_filename;
        }

        $documentPath = "/documents/{$documentName}";

        return Storage::disk('s3')->delete($documentPath);
    }

    public function getTemporaryUrl(Document $document, DocumentType $type, $expiration = 60)
    {
        $documentName = "{$document->uuid}_{$document->filename}";
        if ($type == DocumentType::Signed) {
            $documentName = $document->signed_filename;
        }

        $documentPath = "/documents/{$documentName}";

        return Storage::disk('s3')->temporaryUrl(
            $documentPath,
            $expiration,
            [
                'ResponseContentDisposition' => 'attachment; filename=' . $documentName
            ]
        );
    }
}
