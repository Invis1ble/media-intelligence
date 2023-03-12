<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\Http;

use Psr\Http\Message\StreamInterface;

interface MultipartStreamFactoryInterface
{
    /**
     * @param array{
     *     name: string,
     *     content: string,
     *     filename: ?string,
     *     content_type: ?string} $data
     */
    public function createStream(array $data, string $boundary): StreamInterface;
}
