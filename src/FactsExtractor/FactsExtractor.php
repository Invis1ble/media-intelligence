<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\FactsExtractor;

use Psr\Http\Client\ClientExceptionInterface;

interface FactsExtractor
{
    /**
     * @return iterable<string>
     *
     * @throws ClientExceptionInterface
     */
    public function extract(string $text, ?string $targetLanguage = null): iterable;
}
