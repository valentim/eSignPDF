<?php

namespace Tests\Unit\Infrastructure\Persistence;

use Tests\TestCase;
use App\Domain\Document\Document;
use App\Infrastructure\Persistence\DocumentRepositoryImpl;
use Illuminate\Support\Facades\Facade;
use Mockery;

class DocumentRepositoryImplTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Facade::clearResolvedInstance(Document::class);
    }
    
    public function testSaveDocument()
    {
        $data = [
            'filename' => 'test.pdf',
            'user_id' => 1,
            'signed_filename' => 'signed_test.pdf',
            'signed_at' => now(),
            'uuid' => 'uuid1'
        ];

        $documentMock = Mockery::mock(Document::class)->makePartial();
        $documentMock->filename = 'test.pdf';
        $documentMock->user_id = 1;
        $documentMock->signed_filename = 'signed_test.pdf';
        $documentMock->signed_at = now();
        $documentMock->uuid = 'uuid1';

        $documentMock->shouldReceive('create')
            ->with($data)
            ->andReturn($documentMock);

        $repository = new DocumentRepositoryImpl($documentMock);
        $document = $repository->create($data);

        $this->assertEquals('test.pdf', $document->filename);
        $this->assertEquals(1, $document->user_id);
        $this->assertEquals('signed_test.pdf', $document->signed_filename);
        $this->assertEquals('uuid1', $document->uuid);
    }

    public function testFindDocumentByUuid()
    {
        $uuid = 'uuid1';


        $documentMock = Mockery::mock(Document::class)->makePartial();
        $documentMock->filename = 'test.pdf';
        $documentMock->user_id = 1;
        $documentMock->signed_filename = 'signed_test.pdf';
        $documentMock->signed_at = now();
        $documentMock->uuid = $uuid;

        $documentMock->shouldReceive('where')
            ->with('uuid', $uuid)
            ->andReturnSelf();
        $documentMock->shouldReceive('first')
            ->andReturn($documentMock);

        $repository = new DocumentRepositoryImpl($documentMock);
        $document = $repository->findByUuid($uuid);

        $this->assertEquals('test.pdf', $document->filename);
        $this->assertEquals(1, $document->user_id);
        $this->assertEquals('signed_test.pdf', $document->signed_filename);
        $this->assertEquals('uuid1', $document->uuid);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
