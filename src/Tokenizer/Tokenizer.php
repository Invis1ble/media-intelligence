<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\Tokenizer;

interface Tokenizer
{
    public function tokenize(string $text): array;
}
