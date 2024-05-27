<?php

namespace Tests\Unit\Infrastructure\Services;

use Tests\TestCase;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Infrastructure\Services\EIDEasyService;
use App\Domain\Document\Document;

class EIDEasyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testDownloadSignedFile()
    {
        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getAttribute')->with('doc_id')->andReturn('1234');
        $document->shouldReceive('setAttribute')->andReturnSelf();

        $document->doc_id = '1234';

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], json_encode(['signed_file_contents' => base64_encode('signed content')])));

        $service = new EIDEasyService($mockClient);
        $result = $service->downloadSignedFile($document);

        $this->assertEquals('signed content', $result);
    }

    public function testPrepareFilesForSigning()
    {
        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getAttribute')->with('uuid')->andReturn('1234');
        $document->shouldReceive('getAttribute')->with('filename')->andReturn('test.pdf');
        $document->shouldReceive('setAttribute')->andReturnSelf();

        $document->uuid = '1234';
        $document->filename = 'test.pdf';

        $fileContent = 'test content';

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], json_encode([
                'doc_id' => '123456',
                'signing_page_url' => 'http://example.com/sign'
            ])));

        $service = new EIDEasyService($mockClient);
        [$doc_id, $signing_page_url] = $service->prepareFilesForSigning($fileContent, $document);

        $this->assertEquals('123456', $doc_id);
        $this->assertEquals('http://example.com/sign', $signing_page_url);
    }
}
