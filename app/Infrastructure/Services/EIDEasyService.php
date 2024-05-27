<?php

namespace App\Infrastructure\Services;
use GuzzleHttp\Client;
use Carbon\Carbon;

class EIDEasyService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function downloadSignedFile($document)
    {
        $response = $this->client->post('https://test.eideasy.com/api/signatures/download-signed-file', [
            'headers' => [
                'Content-Type' => "application/json",
            ],
            'json' => [
                'secret' => "0s37f8TrEUmFfPWl8STSWXhfwtpsEtF6",
                'client_id' => "mK0T3X4uX3tzrKqlTFO66nyj2zoeyk3r",
                'doc_id' => $document->doc_id,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $signedFileContents = base64_decode($data['signed_file_contents']);

        return $signedFileContents;
    }

    public function prepareFilesForSigning($fileContent, $document)
    {
        $response = $this->client->post('https://test.eideasy.com/api/signatures/prepare-files-for-signing', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'secret' => '0s37f8TrEUmFfPWl8STSWXhfwtpsEtF6',
                'client_id' => 'mK0T3X4uX3tzrKqlTFO66nyj2zoeyk3r',
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