Media Intelligence
==================

This repository contains code for media analysis and processing.

Dependencies
------------

- [yt-dlp](https://github.com/yt-dlp/yt-dlp): A command-line program to download videos from YouTube.com and a few more sites.
- [ffmpeg](https://www.ffmpeg.org/) required by `yt-dlp` for post-processing tasks.

Installation
------------

To install this package, you can use Composer:

```bash
composer require invis1ble/media-intelligence
```

or just add it as a dependency in your `composer.json` file:

```json

{
    "require": {
        "invis1ble/media-intelligence": "^0.1"
    }
}
```

After adding the above line, run the following command to install the package:

```bash
composer install
```

Usage
-----

To use most of the tools you need to set [OpenAI API Key](https://platform.openai.com/account/api-keys)

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Emgag\Flysystem\Tempdir;
use GuzzleHttp\Client;
use Invis1ble\MediaIntelligence\AudioExtractor\YtDlpAudioExtractor;
use Invis1ble\MediaIntelligence\FactsExtractor\OpenAiFactsExtractor;
use Invis1ble\MediaIntelligence\SpeechToText\OpenAiSpeechToTextTransformer;
use Invis1ble\MediaIntelligence\VideoToFacts\Application;

// Set here your own Open AI Token.
// Visit https://platform.openai.com/account/api-keys for more details.
$openAiApiKey = '';

$client = new Client();

$application = new Application(
    audioExtractor: new YtDlpAudioExtractor(),
    speechToTextTransformer: new OpenAiSpeechToTextTransformer($client, $openAiApiKey),
    factsExtractor: new OpenAiFactsExtractor($client, $openAiApiKey),
    audioTargetDirectory: new SplFileInfo((new Tempdir())->getPath()),
);


// Usage: extract facts from the video
$facts = $application->run('https://www.youtube.com/shorts/j_TLyMPGDKk'); // array with extracted facts from the video
```


License
-------

[The MIT License](./LICENSE)
