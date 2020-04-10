path: blob/master/src
source: Video/VideoAnalyzer.php

The VideoAnalyzer service acts as a wrapper over ffmpeg and will analyze a video stream.
It does not query video metadata (like ffprobe or the `Video\VideoInfoReader`) but really
reads the video to infer some characteristics (currently only interlace detection is implemented...).


```php
<?php
use Soluble\MediaTools\Video\Config\FFMpegConfig;
use Soluble\MediaTools\Video\Exception\AnalyzerExceptionInterface;
use Soluble\MediaTools\Video\VideoAnalyzer;

$analyzer = new VideoAnalyzer(new FFMpegConfig('/path/to/ffmpeg'));


try {
    $interlaceGuess = $analyzer->detectInterlacement(
        '/path/input.mov',
        // Optional:
        //   $maxFramesToAnalyze, default: 1000
        $maxFramesToAnalyze = 200
    );

} catch(AnalyzerExceptionInterface $e) {
    // See chapter about exception !!!
}

$interlaced = $interlaceGuess->isInterlaced(
    // Optional:
    //  $threshold, default 0.25 (if >=25% interlaced frames, then true)
    0.25
);

```

### Requirements

You'll need to have ffmpeg [installed](./install-ffmpeg.md) on your system.

### Initialization

The [VideoAnalyzer](https://github.com/soluble-io/soluble-mediatools/blob/master/src/Video/VideoAnalyzer.php)
requires an [`FFMpegConfig`](https://github.com/soluble-io/soluble-mediatools/blob/master/src/Video/Config/FFMpegConfig.php) object as first parameter.
This is where you set the location of the ffmpeg binary, the number of threads you allow for conversions
and the various timeouts if needed. The second parameter can be used to inject any psr-3 compatible ==logger==.

```php
<?php
use Soluble\MediaTools\Video\Config\{FFMpegConfig, FFMpegConfigInterface};
use Soluble\MediaTools\Video\VideoAnalyzer;

$converter = new VideoAnalyzer(
    // @param FFMpegConfigInterface
    new FFMpegConfig(
        // (?string) - path to ffmpeg binary (default: ffmpeg/ffmpeg.exe)
        $binary = null,
        // (?int)    - ffmpeg default threads (null: single-thread)
        $threads = null,
        // (?float)  - max time in seconds for ffmpeg process (null: disable)
        $timeout = null,
        // (?float)  - max idle time in seconds for ffmpeg process
        $idleTimeout = null,
        // (array)   - additional environment variables
        $env = []
    ),
    // @param ?\Psr\Log\LoggerInterface - Default to `\Psr\Log\NullLogger`.
    $logger = null
);
```

??? tip "Tip: initialize in a container (psr-11)"
    It's a good idea to register services in a container.
    Depending on available framework integrations, you may have a look to the [`Video\VideoAnalyzerFactory`](https://github.com/soluble-io/soluble-mediatools/blob/master/src/Video/VideoAnalyzerFactory.php)
    and/or [`FFMpegConfigFactory`](https://github.com/soluble-io/soluble-mediatools/blob/master/src/Video/Config/FFMpegConfigFactory.php) to get an example based on a psr-11 compatible container.
    See also the provided default [configuration](https://github.com/soluble-io/soluble-mediatools/blob/master/config/soluble-mediatools.config.php) file.


### Usage

#### Interlacement detection


```php
<?php
use Soluble\MediaTools\Video\Config\FFMpegConfig;
use Soluble\MediaTools\Video\Exception\AnalyzerExceptionInterface;
use Soluble\MediaTools\Video\VideoAnalyzer;

$analyzer = new VideoAnalyzer(new FFMpegConfig('/path/to/ffmpeg'));

try {
    $interlaceGuess = $analyzer->detectInterlacement(
        '/path/input.mov',
        // Optional:
        //   $maxFramesToAnalyze, default: 1000
        //   (at 25fps -> 40 seconds)
        $maxFramesToAnalyze = 1000
    );

} catch(AnalyzerExceptionInterface $e) {
    // See chapter about exception !!!
}

$interlaced = $interlaceGuess->isInterlaced(
    // Optional:
    //  $threshold, default 0.25 (if >=25% interlaced frames, then true)
    0.25
);

```

#### Exceptions

You can safely catch exceptions with the generic `Soluble\MediaTools\Video\Exception\ExceptionInterface`,
alternatively you can also :


```php
<?php
use Soluble\MediaTools\Video\VideoAnalyzer;
use Soluble\MediaTools\Video\Exception as VE;

/** @var VideoAnalyzer $analyzer */

try {
    $interlaceGuess = $analyzer->detectInterlacement(
        '/path/input.mov'
    );

} catch(VE\MissingInputFileException $e) {

    // 'i.mov does not exists

    echo $e->getMessage();

} catch(

    // The following 3 exeptions are linked to process
    // failure 'ffmpeg exit code != 0) and implements
    //
    // - `VE\ConversionProcessExceptionInterface`
    //        (* which extends Mediatools\Common\Exception\ProcessExceptionInterface)
    //
    // in case you want to catch them all-in-once

      VE\ProcessFailedException
    | VE\ProcessSignaledException
    | VE\ProcessTimedOutException $e)
{

    echo $e->getMessage();

    // Because they implement ProcessExceptionInterface
    // we can get a reference to the executed (symfony) process:

    $process = $e->getProcess();
    echo $process->getExitCode();
    echo $process->getErrorOutput();

} catch(VE\ConverterExceptionInterface $e) {

    // Other exceptions can be
    //
    // - VE\RuntimeException
    // - VE\InvalidParamException (should not happen)
}

```

### Recipes

#### Convert with deinterlace detection

```php
<?php

use Soluble\MediaTools\Video\VideoAnalyzer;
use Soluble\MediaTools\Video\Filter\{VideoFilterChain, Hqdn3DVideoFilter, YadifVideoFilter};

/** @var VideoAnalyzer $analyzer */

$interlaceGuess = $analyzer->detectInterlacement(
            '/path/input_video.mov',
            // $max_frames_to_analyze:
            //  - Less is faster... but let's assume some older
            //    videos starts with black screen... 1500 at
            //    25fps = 60 seconds
            1500
);

$convertParams = (new \Soluble\MediaTools\Video\VideoConvertParams())
                    ->withVideoCodec('libvpx-vp9')
                    ->withVideoBitrate('750k');

// Add the deint and denoise filters only if 40% frames have been
// detected as interlaced

if ($interlaceGuess->isInterlaced($threshold=0.4)) {
    $convertParams = $convertParams->withVideoFilter(
        new VideoFilterChain([
            // This will deinterlace
            new YadifVideoFilter(),
            // This will slightly denoise
            new Hqdn3DVideoFilter()
        ])
    );
}

$converter->convert(
    '/path/inputFile.mov',
    '/path/outputFile.webm',
    $convertParams
);

```
