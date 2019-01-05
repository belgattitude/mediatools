<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2019 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace Soluble\MediaTools\Video;

use Soluble\MediaTools\Common\Exception\IOException;
use Soluble\MediaTools\Common\Exception\JsonParseException;

class VideoInfo implements VideoInfoInterface
{
    public const STREAM_TYPE_AUDIO = 'audio';
    public const STREAM_TYPE_VIDEO = 'video';
    public const STREAM_TYPE_DATA  = 'data';

    /** @var array */
    protected $metadata;

    /** @var string */
    protected $file;

    public function __construct(string $file, array $metadata)
    {
        $this->metadata = $metadata;
        $this->file     = $file;
    }

    public static function createFromFFProbeJson(string $file, string $ffprobeJson): self
    {
        if (trim($ffprobeJson) === '') {
            throw new JsonParseException('Cannot parse empty json string');
        }
        $decoded = json_decode($ffprobeJson, true);
        if ($decoded === null) {
            throw new JsonParseException('Cannot parse json');
        }

        return new self($file, $decoded);
    }

    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @throws IOException
     */
    public function getFileSize(): int
    {
        $size = @filesize($this->file);
        if ($size === false) {
            throw new IOException(sprintf(
                'Cannot get filesize of file %s',
                $this->file
            ));
        }

        return $size;
    }

    public function getFormatName(): string
    {
        return $this->metadata['format']['format_name'];
    }

    public function countStreams(): int
    {
        return $this->metadata['format']['nb_streams'];
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getDuration(): float
    {
        return (float) ($this->metadata['format']['duration'] ?? 0.0);
    }

    public function getDimensions(): array
    {
        return [
            'width'  => $this->getWidth(),
            'height' => $this->getHeight(),
        ];
    }

    public function getWidth(): int
    {
        $videoStream = $this->getVideoStreamInfo();

        return (int) ($videoStream['width'] ?? 0);
    }

    public function getHeight(): int
    {
        $videoStream = $this->getVideoStreamInfo();

        return (int) ($videoStream['height'] ?? 0);
    }

    public function getNbFrames(): int
    {
        $videoStream = $this->getVideoStreamInfo();

        return (int) ($videoStream['nb_frames'] ?? 0);
    }

    public function getBitrate(): int
    {
        $videoStream = $this->getVideoStreamInfo();

        return (int) ($videoStream['bit_rate'] ?? 0);
    }

    public function getAudioStreamInfo(): ?array
    {
        return $this->getStreamsByType()[self::STREAM_TYPE_AUDIO] ?? null;
    }

    public function getVideoStreamInfo(): ?array
    {
        return $this->getStreamsByType()[self::STREAM_TYPE_VIDEO] ?? null;
    }

    protected function getStreamsByType(): array
    {
        $streams = $this->metadata['streams'] ?? [];
        foreach ($streams as $stream) {
            $type = mb_strtolower($stream['codec_type']);
            switch ($type) {
                case self::STREAM_TYPE_VIDEO:
                    $streams['video'] = $stream;
                    break;
                case self::STREAM_TYPE_AUDIO:
                    $streams['audio'] = $stream;
                    break;
                case self::STREAM_TYPE_DATA:
                    $streams['data'] = $stream;
                    break;
                default:
                    throw new \Exception(sprintf('Does not support codec_type "%s"', $type));
            }
        }

        return $streams;
    }
}
