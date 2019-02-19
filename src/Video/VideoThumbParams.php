<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2019 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace Soluble\MediaTools\Video;

use Soluble\MediaTools\Video\Adapter\FFMpegCLIValueInterface;
use Soluble\MediaTools\Video\Exception\InvalidArgumentException;
use Soluble\MediaTools\Video\Exception\UnsetParamException;
use Soluble\MediaTools\Video\Filter\Type\VideoFilterInterface;

class VideoThumbParams implements VideoThumbParamsInterface
{
    /** @var array<string, bool|string|int|VideoFilterInterface|FFMpegCLIValueInterface> */
    protected $params = [];

    /**
     * @param array<string, bool|string|int|VideoFilterInterface|FFMpegCLIValueInterface> $params
     *
     * @throws InvalidArgumentException in case of unsupported option
     */
    public function __construct(array $params = [])
    {
        $this->ensureSupportedParams($params);
        $this->params = $params;
    }

    /**
     * @param float $time time in seconds, decimals are milli
     *
     * @return VideoThumbParams
     */
    public function withTime(float $time): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_SEEK_TIME => new SeekTime($time),
        ]));
    }

    public function withSeekTime(SeekTime $seekTime): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_SEEK_TIME => $seekTime,
        ]));
    }

    public function withVideoFilter(VideoFilterInterface $videoFilter): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_VIDEO_FILTER => $videoFilter,
        ]));
    }

    /**
     * Set the underlying encoder quality scale. (-qscale:v <int>, alias to -q:v <int>).
     *
     * @param int $qualityScale a number interpreted by the encoder, generally 1-5
     */
    public function withQualityScale(int $qualityScale): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_QUALITY_SCALE => $qualityScale,
        ]));
    }

    /**
     * @param int $frame take this frame
     */
    public function withFrame(int $frame): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_WITH_FRAME => $frame,
        ]));
    }

    /**
     * Add with overwrite option (default).
     *
     * @see self::withNoOverwrite()
     */
    public function withOverwrite(): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_OVERWRITE => true,
        ]));
    }

    /**
     * Add protection against output file overwriting.
     *
     * @see self::witoOverwrite()
     */
    public function withNoOverwrite(): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_OVERWRITE => false,
        ]));
    }

    public function withOutputFormat(string $outputFormat): self
    {
        return new self(array_merge($this->params, [
            self::PARAM_OUTPUT_FORMAT => $outputFormat,
        ]));
    }

    /**
     * Set a built-in param...
     *
     * @param bool|string|int|VideoFilterInterface|FFMpegCLIValueInterface $paramValue
     *
     * @throws InvalidArgumentException in case of unsupported builtin param
     *
     * @return self (static analysis the trick is to return 'self' instead of interface)
     */
    public function withBuiltInParam(string $paramName, $paramValue): VideoThumbParamsInterface
    {
        return new self(array_merge($this->params, [
            $paramName => $paramValue,
        ]));
    }

    /**
     * @return self (For static analysis the trick is to return 'self' instead of interface)
     */
    public function withoutParam(string $paramName): VideoThumbParamsInterface
    {
        $ao = (new \ArrayObject($this->params));
        if ($ao->offsetExists($paramName)) {
            $ao->offsetUnset($paramName);
        }

        return new self($ao->getArrayCopy());
    }

    /**
     * Return the internal array holding params.
     *
     * @return array<string,bool|string|int|VideoFilterInterface|FFMpegCLIValueInterface>
     */
    public function toArray(): array
    {
        return $this->params;
    }

    public function isParamValid(string $paramName): bool
    {
        return in_array($paramName, self::BUILTIN_PARAMS, true);
    }

    /**
     * Return a param, throw an exception if the param has not been defined yet or
     * use $default if it was set.
     *
     * @param mixed $default Will return default value instead of throwing exception
     *
     * @return bool|string|int|VideoFilterInterface|FFMpegCLIValueInterface|null
     *
     * @throws UnsetParamException
     */
    public function getParam(string $paramName, $default = null)
    {
        if (!$this->hasParam($paramName)) {
            if ($default !== null) {
                return $default;
            }

            throw new UnsetParamException(sprintf(
                'Cannot get param \'%s\', it has not been set',
                $paramName
            ));
        }

        return $this->params[$paramName];
    }

    public function hasParam(string $paramName): bool
    {
        return array_key_exists($paramName, $this->params);
    }

    /**
     * Ensure that all params are supported.
     *
     * @param array<string, bool|string|int|VideoFilterInterface|FFMpegCLIValueInterface> $params
     *
     * @throws InvalidArgumentException in case of unsupported option
     */
    protected function ensureSupportedParams(array $params): void
    {
        foreach ($params as $paramName => $paramValue) {
            if (!$this->isParamValid($paramName)) {
                throw new InvalidArgumentException(
                    sprintf('Unsupported param "%s" given.', $paramName)
                );
            }
        }
    }
}
