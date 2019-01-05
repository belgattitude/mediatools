<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2019 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace Soluble\MediaTools\Video;

interface VideoInfoInterface
{
    /**
     * Return the filesize.
     */
    public function getFileSize(): int;

    /**
     * Return the file container format name.
     */
    public function getFormatName(): string;

    /**
     * Return the number of streams.
     */
    public function countStreams(): int;

    /**
     * Return original file path.
     */
    public function getFile(): string;

    /**
     * Return total duration in seconds (decimals shows milliseconds).
     */
    public function getDuration(): float;

    /**
     * @return array<string, int> associative array with 'height' and 'width'
     */
    public function getDimensions(): array;

    /**
     * Return main video stream width.
     */
    public function getWidth(): int;

    /**
     * Return main video stream height.
     */
    public function getHeight(): int;

    /**
     * Return bumber of frames.
     */
    public function getNbFrames(): int;

    /**
     * Return video bitrate.
     */
    public function getBitrate(): int;
}
