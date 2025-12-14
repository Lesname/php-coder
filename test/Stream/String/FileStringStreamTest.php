<?php

declare(strict_types=1);

namespace LesCoderTest\Stream\String;

use LesCoder\Stream\String\FileStringStream;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileStringStream::class)]
class FileStringStreamTest extends TestCase
{
    public function testSimpleStream(): void
    {
        $stream = new FileStringStream(
            __DIR__ . '/stub/file-string-stream.test.txt',
            3,
        );

        self::assertTrue($stream->isActive());
        self::assertSame('L', $stream->current());
        self::assertSame('Lo', $stream->current(2));

        $stream->next(2);
        self::assertSame('rem', $stream->current(3));
        $stream->next(3);

        self::assertSame(PHP_EOL, $stream->current());
        $stream->next();

        self::assertSame('Ipsum', $stream->current(5));
        $stream->next(5);

        self::assertFalse($stream->isActive());
    }
}
