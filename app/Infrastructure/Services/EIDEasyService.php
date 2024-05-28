<?php

namespace App\Infrastructure\Services;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EIDEasyService
{
    private Client $client;
    private $clientId;
    private $clientSecret;
    private $url;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->clientSecret = config('services.eideasy.client_id');
        $this->clientId = config('services.eideasy.client_secret');
        $this->url = config('services.eideasy.url');
    }

    public function downloadSignedFile($document)
    {
        $payload = [
            'headers' => [
                'Content-Type' => "application/json",
            ],
            'json' => [
                'secret' => $this->clientSecret,
                'client_id' => $this->clientId,
                'doc_id' => $document->doc_id,
            ],
        ];

        Log::info('Payload for downloadSignedFile', $payload);

        try {
            $response = $this->client->post("{$this->url}/api/signatures/download-signed-file", $payload);

            $content = $response->getBody()->getContents();

            Log::info('Response from downloadSignedFile', ['content' => $content]);

            $data = json_decode($content, true);
            $signedFileContents = base64_decode($data['signed_file_contents']);

            return $signedFileContents;
        } catch (\Exception $e) {
            Log::error('Failed to download signed file', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function prepareFilesForSigning($fileContent, $document)
    {
        $payload = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'secret' => $this->clientSecret,
                'client_id' => $this->clientId,
                'signature_redirect' => url("/documents/$document->uuid/callback"),
                'noemails' => true,
                'container_type' => "pdf",
                'show_visual' => true,
                'notification_state' => Carbon::now(),
                'return_method_configs' => true,
                'files' => [
                    [
                        'fileName' => $document->filename,
                        'mimeType' => "application/pdf",
                        'fileContent' => base64_encode($fileContent),
                    ]
                ]
            ],
        ];

        Log::info('Payload for prepareFilesForSigning', $payload);
        
        try {
            $response = $this->client->post("{$this->url}/api/signatures/prepare-files-for-signing", $payload);

            $content = $response->getBody()->getContents();

            Log::info('Response from prepareFilesForSigning', ['content' => $content]);

            $data = json_decode($content, true);

            return [
                $data['doc_id'],
                $data['signing_page_url']
            ];
        } catch (\Exception $e) {
            Log::error('Failed to prepare files for signing', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}