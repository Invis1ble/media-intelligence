<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;

readonly class OpenAiFactsExtractor implements FactsExtractor
{
    public function __construct(
        private ClientInterface $client,
        private string $authToken,
    ) {
    }

    public function extract(string $text): iterable
    {
        $prompt = $this->buildPrompt($text);
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

        $content = (string)$response->getBody();
        $data = json_decode($content, true);

        return $this->parseFacts($data['choices'][0]['text']);
    }

    private function parseFacts(string $text): iterable
    {
        $facts = preg_replace('#^\s*Facts:\s*#s', '', $text);

        return array_map(
            fn (string $line): string => preg_replace('#^(?:[0-9]+\.|[•-])\s*#', '', $line),
            explode("\n", $facts),
        );
    }

    private function buildPrompt(string $text): string
    {
        return "Extract facts from the following text: \"$text\"";
    }
}
