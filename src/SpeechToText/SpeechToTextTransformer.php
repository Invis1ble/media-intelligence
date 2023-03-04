<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\SpeechToText;

use Psr\Http\Message\StreamInterface;
use SplFileInfo;

interface SpeechToTextTransformer
{
    public function transform(SplFileInfo | StreamInterface $speech): string;
}
