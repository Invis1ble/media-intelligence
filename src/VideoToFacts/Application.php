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
        $audio = $this->audioExtractor->extract(Utils::uriFor($sourceUrl), $this->audioTargetDirectory);
        $text = $this->speechToTextTransformer->transform($audio);
        $facts = $this->factsExtractor->extract($text);

        return $facts;
    }
}
