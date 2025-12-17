<?php

declare(strict_types=1);

namespace LesCoderTest\Stream\Lexical;

use LesCoder\Stream\Lexical\ArrayLexicalStream;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

#[CoversClass(ArrayLexicalStream::class)]
class ArrayLexicalStreamTest extends TestCase
{
    public function testNext(): void
    {
        $f = $this->createMock(Lexical::class);
        $s = $this->createMock(Lexical::class);

        $stream = new ArrayLexicalStream(
            [
                $f,
                $s,
            ],
        );

        self::assertSame($f, $stream->current());

        $stream->next();

        self::assertSame($s, $stream->current());
        self::assertTrue($stream->isActive());
        self::assertFalse($stream->isEnd());

        $stream->next();

        self::assertFalse($stream->isActive());
        self::assertTrue($stream->isEnd());
    }

    public function testLookahead(): void
    {
        $f = $this->createMock(Lexical::class);
        $s = $this->createMock(Lexical::class);
        $t = $this->createMock(Lexical::class);

        $stream = new ArrayLexicalStream(
            [
                $f,
                $s,
                $t,
            ],
        );

        self::assertSame($f, $stream->current());
        self::assertSame($s, $stream->lookahead());
        self::assertSame($t, $stream->lookahead(2));
        self::assertSame($f, $stream->current());

        $stream->next();
        self::assertSame($s, $stream->current());
        self::assertSame($t, $stream->lookahead());
        self::assertSame(null, $stream->lookahead(2));

        $stream->next();
        self::assertSame($t, $stream->current());
        self::assertSame(null, $stream->lookahead());
    }
}
