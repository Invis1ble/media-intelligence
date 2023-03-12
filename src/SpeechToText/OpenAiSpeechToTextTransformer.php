<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\SpeechToText;

use Invis1ble\MediaIntelligence\Http\MultipartStreamFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SplFileInfo;

class OpenAiSpeechToTextTransformer implements SpeechToTextTransformer, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly UriFactoryInterface $uriFactory,
        private readonly MultipartStreamFactoryInterface $streamFactory,
        public string $apiKey,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transform(SplFileInfo | StreamInterface $speech): string
    {
        $this->logger?->info('Transforming audio to the text');

        if ($speech instanceof SplFileInfo) {
            $file = $speech->openFile();
            $content = $file->fread($file->getSize());
        } else {
            $content = (string)$speech;
        }

        $data = [
            [
                'name' => 'model',
                'content' => 'whisper-1',
            ],
            [
                'name' => 'response_format',
                'content' => 'text',
            ],
            [
                'name' => 'file',
                'content' => $content,
                'filename' => 'speech.mp3',
            ],
        ];

        $boundary = $this->createBoundary();
        $body = $this->streamFactory->createStream($data, $boundary);

        $request = $this->requestFactory->createRequest(
            'POST',
            $this->uriFactory->createUri('https://api.openai.com/v1/audio/transcriptions'),
        )
            ->withHeader('Authorization', "Bearer $this->apiKey")
            ->withHeader('Content-Type', "multipart/form-data; boundary=\"$boundary\"")
            ->withBody($body);

        $text = (string)$this->client->sendRequest($request)
            ->getBody();

        $this->logger?->debug('Text: {text}', ['text' => $text]);

        return $text;
    }

    private function createBoundary(): string
    {
        return bin2hex(random_bytes(40));
    }
}
