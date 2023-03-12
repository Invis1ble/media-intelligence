<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\Http;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

readonly class MultipartStreamFactory implements MultipartStreamFactoryInterface
{
    public function __construct(private StreamFactoryInterface $streamFactory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createStream(array $data, string $boundary): StreamInterface
    {
        $content = '';

        foreach ($data as $item) {
            $content .= "--$boundary\r\n" .
                "Content-Disposition: form-data; name=\"{$item['name']}\"";

            if (isset($item['filename'])) {
                $content .= "; filename=\"{$item['filename']}\"";
            }

            $content .= "\r\n";

            if (isset($item['content_type'])) {
                $content .= "Content-Type: {$item['content_type']}\r\n";
            }

            $content .= "\r\n" .
                "{$item['content']}\r\n";
        }

        $content .= "--$boundary--";

        return $this->streamFactory->createStream($content);
    }
}
