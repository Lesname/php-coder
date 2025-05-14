<?php
declare(strict_types=1);

namespace LesCoderTest\Stream\CodeToken;

use LesCoder\Token\CodeToken;
use LesCoder\Stream\CodeToken\ArrayCodeTokenStream;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArrayCodeTokenStream::class)]
class ArrayCodeTokenStreamTest extends TestCase
{
    public function testWalking(): void
    {
        $one = $this->createMock(CodeToken::class);
        $two = $this->createMock(CodeToken::class);

        $stream = new ArrayCodeTokenStream([$one, $two]);

        self::assertTrue($stream->isActive());
        self::assertFalse($stream->isEnd());
        self::assertSame($one, $stream->current());
        $stream->next();

        self::assertTrue($stream->isActive());
        self::assertFalse($stream->isEnd());
        self::assertSame($two, $stream->current());
        $stream->next();

        self::assertFalse($stream->isActive());
        self::assertTrue($stream->isEnd());
    }
}
