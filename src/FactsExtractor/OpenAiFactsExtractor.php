<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OpenAiFactsExtractor implements FactsExtractor, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const TOKEN_LIMIT = 4097;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly OpenAiTokenCounter $tokenCounter,
        public string $authToken,
    ) {
    }

    /**
     * @throws RequestExceptionInterface
     */
    public function extract(string $text, ?string $targetLanguage = null): iterable
    {
        $this->logger?->info('Extracting facts from text');

        $prompt = $this->buildPrompt($text, $targetLanguage);

        $textLength = mb_strlen($text);
        $this->logger?->debug('Text length: {length} chars', ['length' => $textLength]);
        $this->logger?->debug('Prompt: {prompt}', ['prompt' => $prompt]);

        $promptTokensNumber = $this->tokenCounter->count($prompt);

        $this->logger?->debug('Prompt tokens number: {tokens_number} tokens', ['tokens_number' => $promptTokensNumber]);

        $maxTokens = static::TOKEN_LIMIT - $promptTokensNumber;

        $this->logger?->debug('Request max_tokens: {max_tokens}', ['max_tokens' => $maxTokens]);

        try {
            $response = $this->client->post(
                uri: 'https://api.openai.com/v1/completions',
                options: [
                    RequestOptions::HEADERS => [
                        'Authorization' => "Bearer $this->authToken",
                    ],
                    RequestOptions::JSON => [
                        'model' => 'text-davinci-003',
                        'prompt' => $prompt,
                        'temperature' => 0,
                        'max_tokens' => $maxTokens,
                    ],
                ],
            );
        } catch (RequestException $exception) {
            $this->logger?->error('Error occurred during completion request: {error}', [
                'response' => (string)$exception->getResponse()->getBody(),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ]);

            throw $exception;
        }

        $body = (string)$response->getBody();

        $this->logger?->debug('Response body: {body}', ['body' => $body]);

        $data = json_decode($body, true);

        $this->logger?->debug('Decoded response body: {body}', ['body' => $data]);

        $facts = $this->parseFacts($data['choices'][0]['text']);

        $factsLength = array_reduce(
            $facts,
            fn (int $length, string $fact): int => $length + mb_strlen($fact) + 1,
            0,
        ) - 1;

        $this->logger?->debug('Facts length: {length} chars', ['length' => $factsLength]);

        $compression = round($factsLength / $textLength * 100, 2);

        $this->logger?->info('Compression: {compression}% ({facts_length} / {text_length})', [
            'compression' => round(100 - $compression, 2),
            'facts_length' => $factsLength,
            'text_length' => $textLength,
        ]);

        return $facts;
    }

    private function parseFacts(string $text): array
    {
        $this->logger?->debug('Parsing facts from text: {text}', ['text' => $text]);

        $facts = array_map(
            fn (string $line): string => preg_replace('/^[•-]\s*/u', '', $line),
            explode("\n", preg_replace('/^\n\n/', '', $text)),
        );

        $this->logger?->debug('Parsed facts: {facts}', ['facts' => $facts]);

        return $facts;
    }

    private function buildPrompt(string $text, ?string $language = null): string
    {
        if (null === $language) {
            $language = 'the same language as the text';
        }

        return <<<PROMPT
Extract facts from the text as a bullet point list.
Text: """
$text
"""
Write facts in $language.
PROMPT;
    }
}
