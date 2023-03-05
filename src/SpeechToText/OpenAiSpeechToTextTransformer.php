<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\SpeechToText;

use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SplFileInfo;

class OpenAiSpeechToTextTransformer implements SpeechToTextTransformer, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
        public string $authToken,
    ) {
    }

    public function transform(SplFileInfo | StreamInterface $speech): string
    {
        $this->logger?->info('Transforming audio to the text');

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

        $text = (string)$response->getBody();

        $this->logger?->debug('Text: {text}', ['text' => $text]);

        return $text;
    }
}
