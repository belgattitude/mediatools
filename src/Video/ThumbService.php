<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video;

use Soluble\MediaTools\Common\Assert\PathAssertionsTrait;
use Soluble\MediaTools\Common\Exception\FileNotFoundException;
use Soluble\MediaTools\Common\Exception\UnsupportedParamException;
use Soluble\MediaTools\Common\Exception\UnsupportedParamValueException;
use Soluble\MediaTools\Common\Process\ProcessFactory;
use Soluble\MediaTools\Common\Process\ProcessParamsInterface;
use Soluble\MediaTools\Video\Config\FFMpegConfigInterface;
use Soluble\MediaTools\Video\Exception\ConversionExceptionInterface;
use Soluble\MediaTools\Video\Exception\ConversionProcessExceptionInterface;
use Soluble\MediaTools\Video\Exception\InvalidParamException;
use Soluble\MediaTools\Video\Exception\MissingInputFileException;
use Soluble\MediaTools\Video\Exception\ProcessFailedException;
use Soluble\MediaTools\Video\Exception\ProcessSignaledException;
use Soluble\MediaTools\Video\Exception\ProcessTimedOutException;
use Soluble\MediaTools\Video\Exception\RuntimeException;
use Soluble\MediaTools\Video\Filter\Type\VideoFilterInterface;
use Symfony\Component\Process\Exception as SPException;
use Symfony\Component\Process\Process;

class ThumbService implements ThumbServiceInterface
{
    use PathAssertionsTrait;

    /** @var FFMpegConfigInterface */
    protected $ffmpegConfig;

    public function __construct(FFMpegConfigInterface $ffmpegConfig)
    {
        $this->ffmpegConfig = $ffmpegConfig;
    }

    /**
     * Return ready-to-run symfony process object that you can use
     * to `run()` or `start()` programmatically. Useful if you want
     * handle the process your way...
     *
     * @see https://symfony.com/doc/current/components/process.html
     *
     * @throws UnsupportedParamException
     * @throws UnsupportedParamValueException
     */
    public function getSymfonyProcess(string $videoFile, string $thumbnailFile, ?SeekTime $time = null, ?VideoFilterInterface $videoFilter = null, ?ProcessParamsInterface $processParams = null): Process
    {
        $adapter = $this->ffmpegConfig->getAdapter();

        $params = (new ConversionParams());

        if (!$params->hasParam(ConversionParamsInterface::PARAM_THREADS)
            && $adapter->getDefaultThreads() !== null) {
            $params = $params->withBuiltInParam(
                ConversionParamsInterface::PARAM_THREADS,
                $adapter->getDefaultThreads()
            );
        }

        if ($time !== null) {
            // For performance reasons time seek must be
            // made at the beginning of options
            $params = $params->withSeekStart($time);
        }
        $params = $params->withVideoFrames(1);

        if ($videoFilter !== null) {
            $params = $params->withVideoFilter($videoFilter);
        }

        // Quality scale for the mjpeg encoder
        $params->withVideoQualityScale(2);

        $arguments = $adapter->getMappedConversionParams($params);
        $ffmpegCmd = $adapter->getCliCommand($arguments, $videoFile, $thumbnailFile);

        $pp = $processParams ?? $this->ffmpegConfig->getProcessParams();

        return (new ProcessFactory($ffmpegCmd, $pp))();
    }

    /**
     * @throws ConversionExceptionInterface        Base exception class for conversion exceptions
     * @throws ConversionProcessExceptionInterface Base exception class for process conversion exceptions
     * @throws MissingInputFileException
     * @throws ProcessTimedOutException
     * @throws ProcessFailedException
     * @throws ProcessSignaledException
     * @throws RuntimeException
     * @throws InvalidParamException
     */
    public function makeThumbnail(string $videoFile, string $thumbnailFile, ?SeekTime $time = null, ?VideoFilterInterface $videoFilter = null, ?callable $callback = null, ?ProcessParamsInterface $processParams = null): void
    {
        try {
            $this->ensureFileExists($videoFile);

            $process = $this->getSymfonyProcess($videoFile, $thumbnailFile, $time, $videoFilter, $processParams);
            $process->mustRun($callback);
        } catch (FileNotFoundException $e) {
            throw new MissingInputFileException($e->getMessage());
        } catch (UnsupportedParamValueException | UnsupportedParamException $e) {
            throw new InvalidParamException($e->getMessage());
        } catch (SPException\ProcessTimedOutException $e) {
            throw new ProcessTimedOutException($e->getProcess(), $e);
        } catch (SPException\ProcessSignaledException $e) {
            throw new ProcessSignaledException($e->getProcess(), $e);
        } catch (SPException\ProcessFailedException $e) {
            throw new ProcessFailedException($e->getProcess(), $e);
        } catch (SPException\RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
