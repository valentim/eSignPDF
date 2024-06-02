<?php

namespace App\Presentation\Controllers\Documents;

use Illuminate\Http\Request;
use App\Domain\Document\Document;
use App\Application\Document\DocumentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Presentation\Controllers\Controller;
use App\Infrastructure\Services\DocumentType;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    protected $documentService;
    protected $httpClient;

    public function __construct(DocumentService $documentService, Client $httpClient)
    {
        $this->documentService = $documentService;
        $this->httpClient = $httpClient;
    }

    public function index()
    {
        $userId = Auth::id();

        $documents = $this->documentService->getDocuments($userId);

        return $documents;
    }

    public function download(Request $request, Document $document)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_column(DocumentType::cases(), 'value'))],
        ]);

        $documentType = DocumentType::from($validated['type']);

        $temporaryUrl = $this->documentService->getTemporaryDownloadUrl($document, $documentType);

        return response()->json(['url' => $temporaryUrl]);
    }

    public function sign(Request $request, Document $document)
    {
        $file = $this->documentService->getOriginalFile($document, DocumentType::Original);
        $updatedDocument = $this->documentService->signFile($file, Auth::id(), $document);

        return response()->json(['document' => $updatedDocument]);
    }

    public function upload(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:pdf|max:10480',
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            Log::error('Document upload failed due to validation error', ['errors' => $validator->errors()]);

            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $file = $request->file('file');

        $document = $this->documentService->signFile($file, Auth::id());
        
        return response()->json(['document' => $document]);
    }

    public function delete(Document $document)
    {
        $this->documentService->deleteFiles($document);

        return response()->json(['message' => 'File deleted']);
    }

    public function callback(Request $request, Document $document)
    {
        if ($document->signed_file_upload_at === null) {
            $this->documentService->downloadSignedFile($document);
        }
      
        return redirect('/dashboard');
    }
}
