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

class DocumentController extends Controller
{
    protected $documentService;
    protected $httpClient;

    public function __construct(DocumentService $documentService, Client $httpClient)
    {
        $this->documentService = $documentService;
        $this->httpClient = $httpClient;
    }

    /**
     * @OA\Get(
     *     path="/api/documents",
     *     tags={"documents"},
     *     summary="Get all documents",
     *     description="Returns a list of documents",
     *     @OA\Response(
     *         response=200,
     *         description="A list of documents",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Document"))
     *     )
     * )
     */
    public function index()
    {
        return Document::all();
    }

    /**
     * @OA\Post(
     *     path="/api/documents/{document}/download",
     *     tags={"documents"},
     *     summary="Generate a temporary download URL for a document",
     *     description="Generates a temporary download URL for a document based on its type",
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", description="Document type")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Temporary URL generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string")
     *         )
     *     )
     * )
     */
    public function download(Request $request, Document $document)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_column(DocumentType::cases(), 'value'))],
        ]);

        $documentType = DocumentType::from($validated['type']);

        $temporaryUrl = $this->documentService->getTemporaryDownloadUrl($document, $documentType);

        return response()->json(['url' => $temporaryUrl]);
    }

    /**
     * @OA\Post(
     *     path="/api/documents/{document}/sign",
     *     tags={"documents"},
     *     summary="Sign a document",
     *     description="Signs a document and returns the updated document",
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document signed",
     *         @OA\JsonContent(ref="#/components/schemas/Document")
     *     )
     * )
     */
    public function sign(Request $request, Document $document)
    {
        $file = $this->documentService->getOriginalFile($document, DocumentType::Original);
        $updatedDocument = $this->documentService->signFile($file, Auth::id(), $document);

        return response()->json(['document' => $updatedDocument]);
    }

    /**
     * @OA\Post(
     *     path="/api/documents/upload",
     *     tags={"documents"},
     *     summary="Upload a document",
     *     description="Uploads a document and returns the document information",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document uploaded",
     *         @OA\JsonContent(ref="#/components/schemas/Document")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function upload(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:pdf|max:10480',
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $file = $request->file('file');

        $document = $this->documentService->signFile($file, Auth::id());
        
        return response()->json(['document' => $document]);
    }

    /**
     * @OA\Delete(
     *     path="/api/documents/{document}",
     *     tags={"documents"},
     *     summary="Delete a document",
     *     description="Deletes a document and returns a confirmation message",
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="File deleted")
     *         )
     *     )
     * )
     */
    public function delete(Document $document)
    {
        $this->documentService->deleteFiles($document);

        return response()->json(['message' => 'File deleted']);
    }

     /**
     * @OA\Post(
     *     path="/documents/{document}/callback",
     *     tags={"documents"},
     *     summary="Handle document callback",
     *     description="Handles the callback for a document and redirects to the dashboard",
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to dashboard"
     *     )
     * )
     */
    public function callback(Request $request, Document $document)
    {
        if ($document->signed_file_upload_at === null) {
            $this->documentService->downloadSignedFile($document);
        }
      
        return redirect('/dashboard');
    }
}
