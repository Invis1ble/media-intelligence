<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

interface FactsExtractor
{
    /**
     * @return iterable<string>
     */
    public function extract(string $text, ?string $targetLanguage = null): iterable;
}
