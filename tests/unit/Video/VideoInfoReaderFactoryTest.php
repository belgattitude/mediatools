<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2019 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace MediaToolsTest\Video;

use MediaToolsTest\Util\ServicesProviderTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Soluble\MediaTools\Video\Config\FFProbeConfigInterface;
use Soluble\MediaTools\Video\Config\LoggerConfigInterface;
use Soluble\MediaTools\Video\VideoInfoReaderFactory;

class VideoInfoReaderFactoryTest extends TestCase
{
    use ServicesProviderTrait;

    public function setUp(): void
    {
    }

    public function testWithTestLogger(): void
    {
        $logger = new class() implements LoggerConfigInterface {
            public function getLogger(): LoggerInterface
            {
                return new TestLogger();
            }
        };

        $ffprobeConfig = $this->prophesize(FFProbeConfigInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(LoggerConfigInterface::class)->willReturn(true);
        $container->get(LoggerConfigInterface::class)->willReturn($logger);
        $container->get(FFProbeConfigInterface::class)->willReturn($ffprobeConfig->reveal());

        $videoConverter = new VideoInfoReaderFactory();
        $videoConverter->__invoke($container->reveal());
        self::assertTrue(true);
    }
}