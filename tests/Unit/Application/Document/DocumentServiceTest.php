<?php

namespace Tests\Unit\Application\Document;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Infrastructure\Services\AwsS3Service;
use App\Infrastructure\Services\EIDEasyService;
use App\Domain\Document\DocumentRepository;
use App\Application\Document\DocumentService;
use App\Domain\Document\Document;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use App\Infrastructure\Services\DocumentType;

class DocumentServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testUploadDocument()
    {
        $eIDEasyServiceMock = Mockery::mock(EIDEasyService::class);
        $s3ServiceMock = Mockery::mock(AwsS3Service::class);
        $documentRepositoryMock = Mockery::mock(DocumentRepository::class);

        $documentService = new DocumentService($eIDEasyServiceMock, $s3ServiceMock, $documentRepositoryMock);

        Storage::fake('s3');
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $docId = '123456';
        $signingPageUrl = 'http://localhost:8000/dashboard';
        $eIDEasyServiceMock->shouldReceive('prepareFilesForSigning')
            ->once()
            ->andReturn([$docId, $signingPageUrl]);

        $documentMock = Mockery::mock(Document::class)->makePartial();
        $documentMock->filename = 'document.pdf';
        $documentMock->user_id = 1;
        $documentMock->uuid = 'uuid1';
        $documentMock->doc_id = $docId;

        $s3ServiceMock->shouldReceive('uploadFile')
            ->once()
            ->andReturn(true);

        $documentRepositoryMock->shouldReceive('update')
            ->once()
            ->andReturn($documentMock);

        $documentRepositoryMock->shouldReceive('create')
            ->once()
            ->andReturn($documentMock);

        $document = $documentService->signFile($file, 1);

        $this->assertEquals('document.pdf', $document->filename);
        $this->assertEquals('uuid1', $document->uuid);
        $this->assertEquals(1, $document->user_id);
        $this->assertEquals($docId, $document->doc_id);
    }

    public function testFailureUpload()
    {
        $s3ServiceMock = Mockery::mock(AwsS3Service::class);
        $documentRepositoryMock = Mockery::mock(DocumentRepository::class);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'signed_file_contents' => base64_encode('signed file content')
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $eIDEasyService = new EIDEasyService(
            $client
        );
        $documentService = new DocumentService($eIDEasyService, $s3ServiceMock, $documentRepositoryMock);

        $document = new Document();
        $document->filename = 'document.pdf';
        $document->doc_id = '123456';

        $s3ServiceMock->shouldReceive('uploadFile')
            // ->once()
            // ->with("/documents/{$document->filename}", 'signed file content')
            ->andReturn(false);

        $this->expectException(\Exception::class);

        $document = $documentService->downloadSignedFile($document);

    }

    public function testGetTemporaryDownloadUrl()
    {
        $storageDiskMock = Storage::partialMock();

        $storageDiskMock->shouldReceive('disk')
            ->with('s3')
            ->andReturnSelf();
        $storageDiskMock->shouldReceive('temporaryUrl')
            ->once()
            ->andReturn('http://example.com/documents/document.pdf');

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'signing_page_url' => 'http://localhost:8000/dashboard',
                'doc_id' => '123456'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $eIDEasyService = new EIDEasyService(
            $client
        );
        $s3Service = new AwsS3Service();
        $documentRepositoryMock = Mockery::mock(DocumentRepository::class);

        $document = new Document();
        $document->signed_filename = 'document.pdf';
        
        $documentService = new DocumentService($eIDEasyService, $s3Service, $documentRepositoryMock);

        $temporaryUrl = $documentService->getTemporaryDownloadUrl($document, DocumentType::Signed);

        $this->assertEquals('http://example.com/documents/document.pdf', $temporaryUrl);
    }
}
