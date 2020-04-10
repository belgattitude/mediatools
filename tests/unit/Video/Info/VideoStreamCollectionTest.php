<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2020 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace MediaToolsTest\Video\Info;

use MediaToolsTest\Util\FFProbeMetadataProviderTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Soluble\MediaTools\Video\Exception\InvalidStreamMetadataException;
use Soluble\MediaTools\Video\Exception\NoStreamException;
use Soluble\MediaTools\Video\Info\VideoStream;
use Soluble\MediaTools\Video\Info\VideoStreamCollection;
use Soluble\MediaTools\Video\VideoInfo;

class VideoStreamCollectionTest extends TestCase
{
    use FFProbeMetadataProviderTrait;

    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $vfsRoot;

    /** @var \org\bovigo\vfs\vfsStreamFile */
    private $virtualFile;

    public function setUp(): void
    {
        $this->vfsRoot     = vfsStream::setup();
        $this->virtualFile = vfsStream::newFile('mediatools-test-virtual-file')
            ->withContent('A fake file used by mediatools tests')
            ->at($this->vfsRoot);
    }

    public function testVideoStreamsWithData(): void
    {
        $data = $this->getExampleFFProbeData();

        $vi = new VideoInfo($this->getTestFile(), $data);
        $md = $vi->getVideoStreamsMetadata();

        $coll = new VideoStreamCollection($md);
        self::assertEquals(2, $coll->count());
        self::assertInstanceOf(VideoStream::class, $coll->getFirst());

        foreach ($coll as $idx => $videoStream) {
            // @var VideoStream $videoStream
            self::assertEquals($coll->getIterator()[$idx], $videoStream);
            self::assertEquals('video', $videoStream->getCodecType());
        }
    }

    public function testVideoStreamsWithDataThrowsException(): void
    {
        $this->expectException(InvalidStreamMetadataException::class);
        new VideoStreamCollection([0 => 'cool']);
    }

    public function testVideoStreamsWithDataThrowsNoStreamException(): void
    {
        $this->expectException(NoStreamException::class);
        (new VideoStreamCollection([]))->getFirst();
    }

    private function getTestFile(): string
    {
        return $this->virtualFile->url();
    }
}
