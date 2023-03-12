Media Intelligence
==================

This repository contains PSR-compatible code for media analysis and processing.


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
        "invis1ble/media-intelligence": "^2.0"
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

use Invis1ble\MediaIntelligence\VideoToFacts\Application;

// Set here your own OpenAI API Key.
// Visit https://platform.openai.com/account/api-keys for more details.
$openAiApiKey = '';
$audioTargetDirectoryPath = sys_get_temp_dir();

$application = new Application(
    apiKey: $openAiApiKey,
    audioTargetDirectory: new SplFileInfo($audioTargetDirectoryPath),
);

$facts = $application->run('https://www.youtube.com/watch?v=JdMw9lQTNnc');
/** @var iterable<string> $facts List of the extracted facts */

foreach ($facts as $fact) {
    echo "- $fact\n";
}

```


### Logging setup

To set up logging, you need to set a PSR-3-compatible logger for the services that you want to log.
[Monolog](https://github.com/Seldaek/monolog) is recommended as implementation.

```php

// ...

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

// ...

$logger = new Logger(
    name: 'video_to_facts_logger',
    handlers: [
        // write all staff to the file
        new StreamHandler(__DIR__ . '/../logs/video_to_facts.log', Level::Debug),
        // write info and higher to console
        new StreamHandler(STDOUT, Level::Info),
    ],
);

$application = new Application(
    apiKey: $openAiApiKey,
    audioTargetDirectory: new SplFileInfo($audioTargetDirectoryPath),
    debug: true,
);

$application->setLogger($logger);

$facts = $application->run('https://www.youtube.com/watch?v=JdMw9lQTNnc');
/** @var iterable<string> $facts List of the extracted facts */

```

Output of the above code:

![VideoToFacts Application output](https://user-images.githubusercontent.com/1710944/224415770-a28c6822-f55b-49d7-a5f6-3c95e79c583f.png)


### Translation

You can translate the facts to many languages, just pass the language name to the FactsExtractor through application:

```php
$application->run('https://www.youtube.com/watch?v=1sRLDDIRL4U', 'Ukrainian');
```


Known issues and limitations
----------------------------
- At the moment, we are forced to split long videos into shorter segments due to OpenAI API limitations, which reduces
the efficiency and accuracy of content analysis. If you have any suggestions on how to solve this problem,
please do not hesitate to write about it in the Issues, and I will gladly consider your idea.

Stay tuned!


License
-------

[The MIT License](./LICENSE)
