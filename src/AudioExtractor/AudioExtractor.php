<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\AudioExtractor;

use Psr\Http\Message\UriInterface;
use SplFileInfo;

interface AudioExtractor
{
    public function extract(UriInterface $source, ?SplFileInfo $targetDirectory = null): SplFileInfo;
}
