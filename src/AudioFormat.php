<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence;

enum AudioFormat: string
{
    case M4a = 'm4a';
    case Mp3 = 'mp3';
    case Mp4 = 'mp4';
    case Mpeg = 'mpeg';
    case Mpga = 'mpga';
    case Wav = 'wav';
    case Webm = 'webm';
}
