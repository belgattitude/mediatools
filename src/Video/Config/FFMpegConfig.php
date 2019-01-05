<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video\Config;

use Soluble\MediaTools\Common\Process\ProcessParamsInterface;
use Soluble\MediaTools\Video\Adapter\ConverterAdapterInterface;
use Soluble\MediaTools\Video\Adapter\FFMpegAdapter;
use Soluble\MediaTools\Video\Process\ProcessParams;

class FFMpegConfig implements FFMpegConfigInterface
{
    public const DEFAULT_THREADS      = null;
    public const DEFAULT_TIMEOUT      = null;
    public const DEFAULT_IDLE_TIMEOUT = null;
    public const DEFAULT_ENV          = [];

    /** @var string */
    protected $binary;

    /** @var int|null */
    protected $threads;

    /** @var FFMpegAdapter|null */
    protected $ffmpegAdapter;

    /** @var ProcessParams */
    protected $processParams;

    /**
     * @param string|null               $ffmpegBinary if null will return 'ffmpeg' on linux, 'ffmpeg.exe' on windows
     * @param int|null                  $threads      number fo threads used for conversion, null means single threads, 0 all cores, ....
     * @param float|null                $timeout      max allowed time (in seconds) for conversion, null for no timeout
     * @param float|null                $idleTimeout  max allowed idle time (in seconds) for conversion, null for no timeout
     * @param array<string, string|int> $env          An array of additional env vars to set when running the ffmpeg conversion process
     */
    public function __construct(
        ?string $ffmpegBinary = null,
        ?int $threads = self::DEFAULT_THREADS,
        ?float $timeout = self::DEFAULT_TIMEOUT,
        ?float $idleTimeout = self::DEFAULT_IDLE_TIMEOUT,
        array $env = self::DEFAULT_ENV
    ) {
        $this->binary = $ffmpegBinary ?? self::getPlatformDefaultBinary();

        $this->threads = $threads;

        $this->processParams = new ProcessParams(
            $timeout,
            $idleTimeout,
            $env
        );
    }

    public static function getPlatformDefaultBinary(): string
    {
        return (DIRECTORY_SEPARATOR === '\\') ? 'ffmpeg.exe' : 'ffmpeg';
    }

    public function getBinary(): string
    {
        return $this->binary;
    }

    public function getThreads(): ?int
    {
        return $this->threads;
    }

    public function getProcessParams(): ProcessParamsInterface
    {
        return $this->processParams;
    }

    /**
     * @return FFMpegAdapter
     */
    public function getAdapter(): ConverterAdapterInterface
    {
        if ($this->ffmpegAdapter === null) {
            $this->ffmpegAdapter = new FFMpegAdapter($this);
        }

        return $this->ffmpegAdapter;
    }
}
