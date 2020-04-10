<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2020 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace MediaToolsTest\Video\Filter;

use PHPUnit\Framework\TestCase;
use Soluble\MediaTools\Video\Filter\CropFilter;

class CropFilterTest extends TestCase
{
    public function setUp(): void
    {
    }

    public function testGetFFMpegCLIValue(): void
    {
        self::assertEquals(
            'crop=w=1024:h=800',
            (new CropFilter(1024, 800))->getFFmpegCLIValue()
        );

        self::assertEquals(
            'crop=w=1024:h=800:x=100:y=ih/2:keep_aspect=1:exact=1',
            (new CropFilter(1024, 800, 100, 'ih/2', true, true))->getFFmpegCLIValue()
        );
    }
}
