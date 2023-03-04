<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\AudioExtractor;

use Invis1ble\MediaIntelligence\AudioFormat;
use Psr\Http\Message\UriInterface;
use SplFileInfo;
use UnexpectedValueException;

class YoutubeDlAudioExtractor implements AudioExtractor
{
    private string | null | false $output;

    public function __construct(
        private readonly AudioFormat $format = AudioFormat::Mp3,
        public readonly string $command = 'youtube-dl',
        public bool $audioOnly = false,
        public ?UriInterface $proxy = null,
    ) {
    }

    public function extract(UriInterface $source, ?SplFileInfo $targetDirectory = null): SplFileInfo
    {
        $command = $this->buildCommand($source, $targetDirectory);
        $this->output = shell_exec($command);

        if (!is_string($this->output)) {
            throw new UnexpectedValueException("Unexpected error occurred during command \"$command\" execution.");
        }

        $filename = preg_replace('#\n$#', '', $this->output);

        return new SplFileInfo($filename);
    }

    public function buildCommand(UriInterface $source, ?SplFileInfo $targetDirectory): string
    {
        $options = [
            'audio-quality' => 0,
            'audio-format' => $this->format->value,
            'print' => 'after_move:filepath',
        ];

        if (null !== $targetDirectory) {
            $options['output'] = "\"{$targetDirectory->getPathname()}/%(title)s [%(id)s].%(ext)s\"";
        }

        if (null !== $this->proxy) {
            $options['proxy'] = $this->proxy;
        }

        if (true === $this->audioOnly) {
            $options['format'] = 'bestaudio';
        }

        $optionsString = '';

        foreach ($options as $name => $value) {
            $value = (string)$value;
            $optionsString .= "--$name $value ";
        }

        return "$this->command --extract-audio $optionsString $source";
    }
}
