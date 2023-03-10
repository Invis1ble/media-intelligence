<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\Tokenizer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SentenceTokenizer implements Tokenizer, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function tokenize(string $text): array
    {
        $this->logger?->info('Tokenizing text to sentences');
        $this->logger?->debug('Text: {text}', ['text' => $text]);

        preg_match_all('/(.*?[.!?]+)\s*/u', $text, $matches);

        $sentences = $matches[1] ?? [];

        $this->logger?->debug('Sentences: {sentences}', ['sentences' => $sentences]);

        return $sentences;
    }
}
