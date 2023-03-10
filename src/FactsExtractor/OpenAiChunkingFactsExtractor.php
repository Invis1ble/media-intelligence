<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

use Invis1ble\MediaIntelligence\Tokenizer\ChunkTokenizer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OpenAiChunkingFactsExtractor implements FactsExtractor, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly OpenAiFactsExtractor $factsExtractor,
        private readonly ChunkTokenizer $chunkTokenizer,
    ) {
    }

    public function extract(string $text, ?string $targetLanguage = null): iterable
    {
        $chunks = $this->chunkTokenizer->tokenize($text);

        $textLength = mb_strlen($text);
        $this->logger?->debug('Text length before chunking: {length} chars', ['length' => $textLength]);
        $this->logger?->info('Chunks number: {chunks_number}', ['chunks_number' => count($chunks)]);

        $facts = [];

        foreach ($chunks as $i => $chunk) {
            $this->logger?->info('Processing chunk #{i}', ['i' => $i + 1]);

            foreach ($this->factsExtractor->extract($chunk, $targetLanguage) as $fact) {
                $facts[] = $fact;
            }

            $this->logger?->debug('Chunk #{i} processed', ['i' => $i + 1]);
        }

        $factsLength = array_reduce(
            $facts,
            fn (int $length, string $fact): int => $length + mb_strlen($fact) + 1,
            0,
        ) - 1;

        $this->logger?->debug('Facts total length: {length} chars', ['length' => $factsLength]);

        $factsPercentage = round($factsLength / $textLength * 100, 2);

        $this->logger?->info('Total compression: {compression}% ({facts_length} / {text_length})', [
            'compression' => round(100 - $factsPercentage, 2),
            'facts_length' => $factsLength,
            'text_length' => $textLength,
        ]);

        return $facts;
    }
}
