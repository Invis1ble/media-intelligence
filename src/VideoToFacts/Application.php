<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\VideoToFacts;

use GuzzleHttp\Psr7\Utils;
use Invis1ble\MediaIntelligence\AudioExtractor\AudioExtractor;
use Invis1ble\MediaIntelligence\FactsExtractor\FactsExtractor;
use Invis1ble\MediaIntelligence\SpeechToText\SpeechToTextTransformer;
use SplFileInfo;

final readonly class Application
{
    public function __construct(
        private AudioExtractor $audioExtractor,
        private SpeechToTextTransformer $speechToTextTransformer,
        private FactsExtractor $factsExtractor,
        private ?SplFileInfo $audioTargetDirectory = null,
    ) {
    }

    /**
     * @return iterable<string>
     */
    public function run(string $sourceUrl): iterable
    {
        $this->info("Extracting audio from source $sourceUrl");

        $audio = $this->audioExtractor->extract(Utils::uriFor($sourceUrl), $this->audioTargetDirectory);

        $this->info("Audio saved in \"{$audio->getPathname()}\"");
        $this->info('Transforming extracted audio to the text');

        $text = $this->speechToTextTransformer->transform($audio);

        $this->info('Extracting facts from text');
        echo "$text\n";

        $facts = $this->factsExtractor->extract($text);

        $this->info('Extracted facts');

        foreach ($facts as $fact) {
            echo "- $fact\n";
        }

        return $facts;
    }

    private function info(string $message): void
    {
        $date = date('d.m.Y H:i:s.U');

        echo "[$date - $message]\n";
    }
}
