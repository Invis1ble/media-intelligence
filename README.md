Media Intelligence
==================

This repository contains code for media analysis and processing.


Dependencies
------------

In order to use this package you need to install the following utilities:

- [yt-dlp](https://github.com/yt-dlp/yt-dlp) is a command-line program to download videos from YouTube.com and a few more sites.
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
        "invis1ble/media-intelligence": "^0.2"
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
<?php // ./public/index.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Invis1ble\MediaIntelligence\AudioExtractor\YtDlpAudioExtractor;
use Invis1ble\MediaIntelligence\FactsExtractor\OpenAiFactsExtractor;
use Invis1ble\MediaIntelligence\SpeechToText\OpenAiSpeechToTextTransformer;
use Invis1ble\MediaIntelligence\VideoToFacts\Application;

// Set here your own OpenAI API Key.
// Visit https://platform.openai.com/account/api-keys for more details.
$openAiApiKey = '';
$audioTargetDirectoryPath = sys_get_temp_dir();

$client = new Client();

$application = new Application(
    audioExtractor: new YtDlpAudioExtractor(),
    speechToTextTransformer: new OpenAiSpeechToTextTransformer($client, $openAiApiKey),
    factsExtractor: new OpenAiFactsExtractor($client, $openAiApiKey),
    audioTargetDirectory: new SplFileInfo($audioTargetDirectoryPath),
);


// Usage: extract facts from the video
$facts = $application->run('https://www.youtube.com/watch?v=VjjqRJS7gHY');
/** @var iterable<string> $facts List of the extracted facts */
```

Output of the above code:

![VideoToFacts Application output](https://user-images.githubusercontent.com/1710944/222926850-87526e12-0231-4094-b869-c7758ebecb03.png)


Known issues and limitations
----------------------------
- Logs are written directly to `stdout`. I'm currently working on integrating a logger.
- At the moment, only relatively short videos (no more than 10 minutes) are supported. Work is in progress on this.

Stay tuned!


License
-------

[The MIT License](./LICENSE)
