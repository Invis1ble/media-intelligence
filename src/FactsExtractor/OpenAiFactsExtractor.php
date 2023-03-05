<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OpenAiFactsExtractor implements FactsExtractor, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
        public string $authToken,
    ) {
    }

    public function extract(string $text): iterable
    {
        $this->logger?->info('Extracting facts from text');

        $prompt = $this->buildPrompt($text);

        $this->logger?->debug('Prompt {prompt}', ['prompt' => $prompt]);

        $response = $this->client->post(
            uri: 'https://api.openai.com/v1/completions',
            options: [
                RequestOptions::HEADERS => [
                    'Authorization' => "Bearer $this->authToken",
                ],
                RequestOptions::JSON => [
                    'model' => 'text-davinci-003',
                    'prompt' => $prompt,
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                ],
            ],
        );

        $body = (string)$response->getBody();

        $this->logger?->debug('Response body: {body}', ['body' => $body]);

        $data = json_decode($body, true);

        $this->logger?->debug('Decoded response body: {body}', ['body' => $data]);

        return $this->parseFacts($data['choices'][0]['text']);
    }

    private function parseFacts(string $text): iterable
    {
        $this->logger?->debug('Parsing facts from text: {text}', ['text' => $text]);

        $facts = array_map(
            fn (string $line): string => preg_replace('#^(?:[0-9]+\.|[•-])\s*#', '', $line),
            explode("\n", preg_replace('#^\s*Facts:\s*#s', '', $text)),
        );

        $this->logger?->debug('Parsed facts: {facts}', ['facts' => $facts]);

        return $facts;
    }

    private function buildPrompt(string $text): string
    {
        return "Extract facts from the following text: \"$text\"";
    }
}
