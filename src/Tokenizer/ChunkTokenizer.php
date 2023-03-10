<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\Tokenizer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ChunkTokenizer implements Tokenizer, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly SentenceTokenizer $sentenceTokenizer,
        private readonly int $chunkSizeThreshold,
    ) {
    }

    public function tokenize(string $text): array
    {
        $this->logger?->info('Tokenizing text to chunks');
        $this->logger?->debug('Text: {text}', ['text' => $text]);
        $this->logger?->debug('Chunk size threshold {threshold_size}', ['threshold_size' => $this->chunkSizeThreshold]);

        $sentences = $this->sentenceTokenizer->tokenize($text);

        $chunks = [];
        $chunk = [];
        $chunkSize = 0;
        $sentenceNumber = count($sentences);

        foreach ($sentences as $i => $sentence) {
            $chunk[] = $sentence;
            $chunkSize += strlen($sentence);

            if ($chunkSize > $this->chunkSizeThreshold || $i + 1 === $sentenceNumber) {
                $chunks[] = $chunk;
                $chunk = [];
                $chunkSize = 0;
            }
        }

        $chunks = array_map(
            fn (array $chunk): string => implode(' ', $chunk),
            $chunks,
        );

        $this->logger?->debug('Chunks: {chunks}', ['chunks' => $chunks]);

        return $chunks;
    }
}
