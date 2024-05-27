<?php

namespace Tests\Unit\Infrastructure\Services;

use Tests\TestCase;
use Mockery;
use App\Infrastructure\Services\AwsS3Service;
use App\Domain\Document\Document;
use Illuminate\Support\Facades\Storage;
use App\Infrastructure\Services\DocumentType;

class AwsS3ServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testUploadFile()
    {
        Storage::fake('s3');
        $filePath = 'documents/test.pdf';
        $fileContent = 'test content';

        $s3Service = new AwsS3Service();
        $result = $s3Service->uploadFile($filePath, $fileContent);

        Storage::disk('s3')->assertExists($filePath);
        $this->assertTrue($result);
    }

    public function testDownloadFile()
    {
        Storage::fake('s3');
        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getAttribute')->with('uuid')->andReturn('1234');
        $document->shouldReceive('getAttribute')->with('filename')->andReturn('test.pdf');
        $document->shouldReceive('setAttribute')->andReturnSelf();
        $document->uuid = '1234';
        $document->filename = 'test.pdf';

        Storage::disk('s3')->put('documents/1234_test.pdf', 'test content');

        $s3Service = new AwsS3Service();
        $result = $s3Service->downloadFile($document, DocumentType::Original);

        $this->assertEquals('test content', $result);
    }

    public function testDeleteFile()
    {
        Storage::fake('s3');
        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getAttribute')->with('uuid')->andReturn('1234');
        $document->shouldReceive('getAttribute')->with('filename')->andReturn('test.pdf');
        $document->shouldReceive('setAttribute')->andReturnSelf();

        $document->uuid = '1234';
        $document->filename = 'test.pdf';

        Storage::disk('s3')->put('documents/1234_test.pdf', 'test content');

        $s3Service = new AwsS3Service();
        $result = $s3Service->deleteFile($document, DocumentType::Original);

        $this->assertTrue($result);
        Storage::disk('s3')->assertMissing('documents/1234_test.pdf');
    }

    public function testGetTemporaryUrl()
    {
        Storage::fake('s3');
        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getAttribute')->with('uuid')->andReturn('1234');
        $document->shouldReceive('getAttribute')->with('filename')->andReturn('test.pdf');
        $document->shouldReceive('setAttribute')->andReturnSelf();

        $document->uuid = '1234';
        $document->filename = 'test.pdf';

        $s3Service = new AwsS3Service();
        $mockFilesystem = Mockery::mock(Filesystem::class);
        $mockFilesystem->shouldReceive('temporaryUrl')
            ->once()
            ->andReturn('http://example.com/test.pdf');
        Storage::shouldReceive('disk')
            ->with('s3')
            ->andReturn($mockFilesystem);

        $url = $s3Service->getTemporaryUrl($document, DocumentType::Original);

        $this->assertEquals('http://example.com/test.pdf', $url);
    }
}
