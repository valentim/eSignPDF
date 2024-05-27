<?php

namespace App\Infrastructure\Services;
use GuzzleHttp\Client;
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
        $this->clientSecret = config('EIDEASY_CLIENT_SECRET');
        $this->clientId = config('EIDEASY_CLIENT_ID');
        $this->url = config('EIDEASY_URL');
    }

    public function downloadSignedFile($document)
    {
        $response = $this->client->post("{$this->url}/api/signatures/download-signed-file", [
            'headers' => [
                'Content-Type' => "application/json",
            ],
            'json' => [
                'secret' => $this->clientSecret,
                'client_id' => $this->clientId,
                'doc_id' => $document->doc_id,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $signedFileContents = base64_decode($data['signed_file_contents']);

        return $signedFileContents;
    }

    public function prepareFilesForSigning($fileContent, $document)
    {
        $response = $this->client->post("{$this->url}api/signatures/prepare-files-for-signing", [
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
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            $data['doc_id'],
            $data['signing_page_url']
        ];
    }
}