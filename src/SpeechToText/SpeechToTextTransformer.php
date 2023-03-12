<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\SpeechToText;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;
use SplFileInfo;

interface SpeechToTextTransformer
{
    /**
     * @throws ClientExceptionInterface
     */
    public function transform(SplFileInfo | StreamInterface $speech): string;
}
