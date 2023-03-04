<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\SpeechToText;

use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;
use SplFileInfo;

readonly class OpenAiSpeechToTextTransformer implements SpeechToTextTransformer
{
    public function __construct(
        private ClientInterface $client,
        private string $authToken,
    ) {
    }

    public function transform(SplFileInfo | StreamInterface $speech): string
    {
        if ($speech instanceof SplFileInfo) {
            $speech = Utils::streamFor($speech->openFile('r'));
        }

        $response = $this->client->post(
            uri: 'https://api.openai.com/v1/audio/transcriptions',
            options: [
                RequestOptions::HEADERS => [
                    'Authorization' => "Bearer $this->authToken",
                ],
                RequestOptions::MULTIPART => [
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1',
                    ],
                    [
                        'name' => 'response_format',
                        'contents' => 'text',
                    ],
                    [
                        'name' => 'file',
                        'contents' => $speech->getContents(),
                        'filename' => 'speech.mp3',
                    ],
                ],
            ],
        );

        return (string)$response->getBody();
    }
}
