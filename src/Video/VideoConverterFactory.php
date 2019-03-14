<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2019 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace Soluble\MediaTools\Video;

use Psr\Container\ContainerInterface;
use Soluble\MediaTools\Video\Config\FFMpegConfigInterface;
use Soluble\MediaTools\Video\Logger\LoggerInterface;

final class VideoConverterFactory
{
    public function __invoke(ContainerInterface $container): VideoConverterInterface
    {
        $logger = $container->has(LoggerInterface::class) ? $container->get(LoggerInterface::class) : null;

        return new VideoConverter(
            $container->get(FFMpegConfigInterface::class),
            $logger
        );
    }
}
