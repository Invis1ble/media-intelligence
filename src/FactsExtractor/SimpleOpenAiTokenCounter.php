<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

readonly class SimpleOpenAiTokenCounter implements OpenAiTokenCounter
{
    public function __construct(private float $tokenSizeInBytes = 1.5)
    {
    }

    public function count(string $text): int
    {
        return (int)ceil(strlen($text) / $this->tokenSizeInBytes);
    }

    public function tokensNumberToSizeInBytes(int $tokensNumber): int
    {
        return (int)(ceil($this->tokenSizeInBytes * $tokensNumber));
    }
}
