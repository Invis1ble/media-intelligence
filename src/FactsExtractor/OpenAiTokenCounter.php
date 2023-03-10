<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

interface OpenAiTokenCounter
{
    public function count(string $text): int;

    public function tokensNumberToSizeInBytes(int $tokensNumber): int;
}
