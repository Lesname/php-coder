<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use PHPUnit\Framework\TestCase;
use LesCoder\Stream\String\StringStream;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Specification\ForwardSlashStartSpecification;
use LesCoder\Interpreter\Lexer\Specification\Exception\MissesClosingIdentifier;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\ForwardSlashStartSpecification
 */
class ForwardSlashStartLexSpecificationTest extends TestCase
{
    public function testIsSatisfiedByTrue(): void
    {
        $slash = $this->createMock(StringStream::class);
        $slash->method('current')->willReturn('/');

        $specification = new ForwardSlashStartSpecification();

        self::assertTrue($specification->isSatisfiedBy($slash));
    }

    public function testIsSatisfiedByFalse(): void
    {
        $slash = $this->createMock(StringStream::class);
        $slash->method('current')->willReturn('.');

        $specification = new ForwardSlashStartSpecification();

        self::assertFalse($specification->isSatisfiedBy($slash));
    }

    public function testParseSimpleSlash(): void
    {
        $stream = new DirectStringStream('/ foo');

        $specification = new ForwardSlashStartSpecification();

        self::assertEquals(
            new ForwardSlashLexical(),
            $specification->parse($stream),
        );
    }

    public function testParseSingleLineComment(): void
    {
        $comment = <<<'TXT'
// foo
bar
TXT;

        $stream = new DirectStringStream($comment);

        $specification = new ForwardSlashStartSpecification();

        self::assertEquals(
            new CommentLexical("foo"),
            $specification->parse($stream),
        );
    }

    public function testParseMultiLineComment(): void
    {
        $comment = <<<'TXT'
/*
 * bix
 */
 
Foobar
TXT;

        $stream = new DirectStringStream($comment);

        $specification = new ForwardSlashStartSpecification();

        self::assertEquals(
            new CommentLexical("bix"),
            $specification->parse($stream),
        );
    }

    public function testParseMultiLineCommentMissesClosingIdentifier(): void
    {
        $this->expectException(MissesClosingIdentifier::class);

        $comment = <<<'TXT'
/*
 * bix

 
Foobar
TXT;

        $stream = new DirectStringStream($comment);

        $specification = new ForwardSlashStartSpecification();
        $specification->parse($stream);
    }
}
