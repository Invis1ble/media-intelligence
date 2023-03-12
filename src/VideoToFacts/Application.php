<?php

declare(strict_types=1);

namespace Invis1ble\MediaIntelligence\VideoToFacts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Utils;
use Invis1ble\MediaIntelligence\AudioExtractor\AudioExtractor;
use Invis1ble\MediaIntelligence\AudioExtractor\YtDlpAudioExtractor;
use Invis1ble\MediaIntelligence\FactsExtractor\OpenAiChunkingFactsExtractor;
use Invis1ble\MediaIntelligence\FactsExtractor\OpenAiFactsExtractor;
use Invis1ble\MediaIntelligence\FactsExtractor\SimpleOpenAiTokenCounter;
use Invis1ble\MediaIntelligence\Http\MultipartStreamFactory;
use Invis1ble\MediaIntelligence\SpeechToText\OpenAiSpeechToTextTransformer;
use Invis1ble\MediaIntelligence\Tokenizer\ChunkTokenizer;
use Invis1ble\MediaIntelligence\Tokenizer\SentenceTokenizer;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SplFileInfo;

final class Application implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private readonly AudioExtractor $audioExtractor;

    private readonly OpenAiSpeechToTextTransformer $speechToTextTransformer;

    private readonly OpenAiFactsExtractor $factsExtractor;

    private readonly SentenceTokenizer $sentenceTokenizer;

    private readonly ChunkTokenizer $chunkTokenizer;

    private readonly OpenAiChunkingFactsExtractor $chunkingFactsExtractor;

    public function __construct(
        public string $apiKey,
        private readonly ?SplFileInfo $audioTargetDirectory = null,
        bool $debug = false,
    ) {
        $client = new Client(['debug' => $debug]);
        $this->audioExtractor = new YtDlpAudioExtractor();
        $httpFactory = new HttpFactory();

        $this->speechToTextTransformer = new OpenAiSpeechToTextTransformer(
            client: $client,
            requestFactory: $httpFactory,
            uriFactory: $httpFactory,
            streamFactory: new MultipartStreamFactory($httpFactory),
            apiKey: $this->apiKey,
        );

        $tokenCounter = new SimpleOpenAiTokenCounter();
        $this->factsExtractor = new OpenAiFactsExtractor(
            client: $client,
            requestFactory: $httpFactory,
            uriFactory: $httpFactory,
            streamFactory: $httpFactory,
            tokenCounter: $tokenCounter,
            apiKey: $this->apiKey,
        );

        $this->sentenceTokenizer = new SentenceTokenizer();
        $this->chunkTokenizer = new ChunkTokenizer(
            sentenceTokenizer: $this->sentenceTokenizer,
            chunkSizeThreshold: $tokenCounter->tokensNumberToSizeInBytes(2048),
        );

        $this->chunkingFactsExtractor = new OpenAiChunkingFactsExtractor($this->factsExtractor, $this->chunkTokenizer);
    }

    /**
     * @return iterable<string>
     * @throws ClientExceptionInterface
     */
    public function run(string $sourceUrl, ?string $targetLanguage = null): iterable
    {
        $audio = $this->audioExtractor->extract(Utils::uriFor($sourceUrl), $this->audioTargetDirectory);

        try {
            $text = $this->speechToTextTransformer->transform($audio);
            $facts = $this->chunkingFactsExtractor->extract($text, $targetLanguage);
        } catch (RequestException $exception) {
            $this->logger?->error('HTTP error occurred during request: {error}', [
                'response' => (string)$exception->getResponse()->getBody(),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ]);

            throw $exception;
        }

        return $facts;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
        $this->audioExtractor->setLogger($logger);
        $this->speechToTextTransformer->setLogger($logger);
        $this->factsExtractor->setLogger($logger);
        $this->sentenceTokenizer->setLogger($logger);
        $this->chunkTokenizer->setLogger($logger);
        $this->chunkingFactsExtractor->setLogger($logger);
    }
}
