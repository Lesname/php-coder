<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer;

use PHPUnit\Framework\TestCase;
use LesCoder\ValueObject\Position;
use LesCoder\Stream\String\StringStream;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\TextLexical;
use LesCoder\Interpreter\Lexer\SpecificationCodeLexer;
use LesCoder\Interpreter\Lexer\Specification\Specification;

/**
 * @covers \LesCoder\Interpreter\Lexer\SpecificationCodeLexer
 */
class LexSpecificationCodeLexerTest extends TestCase
{
    public function testTokenize(): void
    {
        $first = $this->createMock(Specification::class);
        $first->expects(self::once())->method('isSatisfiedBy')->willReturn(false);
        $first->expects(self::never())->method('parse');

        $token = $this->createMock(Lexical::class);

        $second = $this->createMock(Specification::class);
        $second->expects(self::once())->method('isSatisfiedBy')->willReturn(true);
        $second->expects(self::once())->method('parse')->willReturn($token);

        $stream = $this->createMock(StringStream::class);
        $stream
            ->method('isActive')
            ->willReturnOnConsecutiveCalls(
                true,
                false,
            );

        $lexer = new SpecificationCodeLexer([$first, $second]);
        $lexicals = $lexer->tokenize($stream);

        self::assertSame($token, $lexicals->current());
    }

    public function testTokenizeNonMatch(): void
    {
        $first = $this->createMock(Specification::class);
        $first->expects(self::once())->method('isSatisfiedBy')->willReturn(false);
        $first->expects(self::never())->method('parse');

        $stream = new DirectStringStream('f');

        $lexer = new SpecificationCodeLexer([$first]);
        $lexicals = $lexer->tokenize($stream);

        self::assertEquals(
            new TextLexical('f'),
            $lexicals->current(),
        );
    }

    public function testTokenizeTextMatch(): void
    {
        $token = $this->createMock(Lexical::class);

        $stream = $this->createMock(StringStream::class);
        $stream->method('current')->willReturn('f');
        $stream
            ->method('isActive')
            ->willReturnOnConsecutiveCalls(
                true,
                true,
                false,
            );

        $first = $this->createMock(Specification::class);
        $first
            ->expects(self::exactly(2))
            ->method('isSatisfiedBy')
            ->willReturnOnConsecutiveCalls(false, true);
        $first->expects(self::once())->method('parse')->willReturn($token);

        $lexer = new SpecificationCodeLexer([$first]);
        $lexicals = $lexer->tokenize($stream);

        $expected = [
            // Stream has no positioning so always 0
            new TextLexical('f'),
            $token,
        ];

        self::assertEquals($expected[0], $lexicals->current());
        $lexicals->next();
        self::assertEquals($expected[1], $lexicals->current());
    }
}
