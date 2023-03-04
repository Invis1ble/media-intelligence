<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\AudioExtractor;

use Invis1ble\MediaIntelligence\AudioFormat;
use Psr\Http\Message\UriInterface;

class YtDlpAudioExtractor extends YoutubeDlAudioExtractor
{
    public function __construct(
        AudioFormat $format = AudioFormat::Mp3,
        string $command = 'yt-dlp',
        $audioOnly = false,
        public ?UriInterface $proxy = null,
    ) {
        parent::__construct($format, $command, $audioOnly, $proxy);
    }
}
