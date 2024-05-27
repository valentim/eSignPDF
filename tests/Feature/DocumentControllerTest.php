<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use App\Domain\Document\Document;
use App\Application\Document\DocumentService;
use App\Domain\User\User;
use App\Infrastructure\Services\AwsS3Service;
use App\Infrastructure\Persistence\DocumentRepositoryImpl;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Http;
use App\Infrastructure\Services\EIDEasyService;


class DocumentControllerTest extends TestCase
{
    use WithFaker;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    
    /** @test */
    public function itCanDownloadDocument()
    {
        $user = User::factory()->make();
        $this->be($user);

        $storageDiskMock = Storage::partialMock();
        $storageDiskMock->shouldReceive('disk')
            ->with('s3')
            ->andReturnSelf();
        $storageDiskMock->shouldReceive('temporaryUrl')
            ->once()
            ->andReturn('http://example.com/file1.pdf');

        $document = Mockery::mock(Document::class)->makePartial();
        $document->shouldReceive('getAttribute')->with('uuid')->andReturn('uuid1');
        $document->uuid = 'uuid1';
        $document->filename = 'file1.pdf';
        $document->path = 'documents/file1.pdf';

        $document->shouldReceive('newQuery->where->first')->andReturn($document);

        $eIDEasyServiceMock = Mockery::mock(EIDEasyService::class);                    
        $awsS3Service = new AwsS3Service();
        $documentRepository = new DocumentRepositoryImpl($document);

        $this->app->instance(Document::class, $document);

        $documentService = new DocumentService($eIDEasyServiceMock, $awsS3Service, $documentRepository);
        $this->app->instance(DocumentService::class, $documentService);

        $response = $this->actingAs($user)->getJson('/api/documents/uuid1/download?type=original');
        $response->assertStatus(200)->assertJson(['url' => 'http://example.com/file1.pdf']);

    }

    /** @test */
    public function itCanSignDocument()
    {
        $user = User::factory()->make();
        $this->be($user);

        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getAttribute')->with('uuid')->andReturn('uuid1');
        $document->shouldReceive('getAttribute')->with('filename')->andReturn('document.pdf');
        $document->shouldReceive('getAttribute')->with('path')->andReturn('documents/document.pdf');
        $document->shouldReceive('resolveRouteBinding')->andReturn($document);
        $document->shouldReceive('jsonSerialize')->andReturn($document);

        $document->shouldReceive('setAttribute')->andReturnSelf();

        $document->id = 1;
        $document->filename = 'document.pdf';
        $document->path = 'documents/document.pdf';

        $document->shouldReceive('update')->andReturn($document);
        $this->app->instance(Document::class, $document);

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

        $awsS3Service = new AwsS3Service();
        $documentRepository = new DocumentRepositoryImpl($document);

        $this->app->instance(DocumentRepository::class, $documentRepository);

        $documentServiceMock = Mockery::mock(DocumentService::class, [$eIDEasyService, $awsS3Service, $documentRepository])->makePartial();
        $documentServiceMock->shouldReceive('signFile')
                            ->andReturn($document);

        $this->app->instance(DocumentService::class, $documentServiceMock);

        Auth::shouldReceive('id')->andReturn(1);
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('guard')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('shouldUse')->andReturnSelf();
        Auth::shouldReceive('userResolver')->andReturn(function () use ($user) {
            return $user;
        });

        Storage::fake('s3');
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $tempFilePath = storage_path('app/documents/' . $document->filename);
        file_put_contents($tempFilePath, File::get($file->getPathname()));


        $this->beforeApplicationDestroyed(function () use ($tempFilePath) {
            @unlink($tempFilePath);
        });

        $httpClientMock = Mockery::mock(Client::class);
        $httpClientMock->shouldReceive('post')
            ->with('https://test.eideasy.com/api/signatures/prepare-files-for-signing3', Mockery::type('array'))
            ->andReturn(new Response(200, [], json_encode([
                'signing_page_url' => 'http://localhost:8000/dashboard',
                'doc_id' => '1234567'
            ])));

        $this->app->instance(Client::class, $httpClientMock);

        $response = $this->postJson('/api/documents/uuid/sign', [
            'file' => $file,
        ]);

        if ($response->status() !== 200) {
            dd($response->status(), $response->content());
        }

        $response->assertStatus(200)
                 ->assertJsonStructure(['document']);
    }

    /** @test */
    public function itCanDeleteDocument()
    {
        $user = User::factory()->make();
        $this->be($user);

        $document = Mockery::mock(Document::class)->makePartial();
        $document->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $document->shouldReceive('delete')->andReturn(true);
        $document->shouldReceive('newQuery->findOrFail')->andReturn($document);
        $document->shouldReceive('newQuery->where->first')->andReturn($document);


        $this->app->instance(Document::class, $document);

        $documentRepositoryMock = Mockery::mock(DocumentRepositoryImpl::class);
        $documentRepositoryMock->shouldReceive('findByUuid')->andReturn($document);
        $documentRepositoryMock->shouldReceive('delete')->andReturn(true);

        $this->app->instance(DocumentRepositoryImpl::class, $documentRepositoryMock);

        $eIDEasyService = new EIDEasyService(
            new Client()
        );

        $s3ServiceMock = Mockery::mock(AwsS3Service::class);

        $documentServiceMock = Mockery::mock(DocumentService::class, [$eIDEasyService, $s3ServiceMock, $documentRepositoryMock])->makePartial();
        $documentServiceMock->shouldReceive('deleteFiles');

        $this->app->instance(DocumentService::class, $documentServiceMock);

        Auth::shouldReceive('id')->andReturn(1);
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('guard')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('setUser')->andReturnSelf();
        Auth::shouldReceive('shouldUse')->andReturnSelf();
        Auth::shouldReceive('userResolver')->andReturn(function () use ($user) {
            return $user;
        });

        $response = $this->actingAs($user)->delete("/api/documents/uuid");

        $response->assertStatus(200);
    }
}