<?php

declare(strict_types=1);

namespace LesCoderTest\Stream\Lexical;

use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\Lexical\IteratorLexicalStream;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IteratorLexicalStream::class)]
class IteratorLexicalStreamTest extends TestCase
{
    public function testNext(): void
    {
        $f = $this->createMock(Lexical::class);
        $s = $this->createMock(Lexical::class);

        $stream = new IteratorLexicalStream(
            (static function () use ($f, $s): iterable {
                yield $f;

                yield $s;
            })(),
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

        $stream = new IteratorLexicalStream(
            (static function () use ($f, $s, $t): iterable {
                yield $f;

                yield $s;

                yield $t;
            })(),
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
