<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification\Typescript;

use RuntimeException;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\AttributeCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Lexer\Lexical\Character\AtSignLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\AttributeParseSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeParseSpecification::class)]
class AttributeParseSpecificationTest extends TestCase
{
    public function testParse(): void
    {
        $code = <<<'TS'
@Foo(
    1,
    null,
)
TS;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification
            ->expects(self::once())
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) {
                    $stream->next();

                    return new ReferenceCodeToken('Foo', 'fiz');
                },
            );

        $expressionCodeTokenOne = $this->createMock(CodeToken::class);
        $expressionCodeTokenTwo = $this->createMock(CodeToken::class);

        $expressionParseSpecification = $this->createMock(ParseSpecification::class);
        $expressionParseSpecification
            ->expects(self::exactly(2))
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) use ($expressionCodeTokenOne, $expressionCodeTokenTwo) {
                    static $i = 0;
                    assert(is_int($i));

                    $stream->next();

                    return match ($i++) {
                        0 => $expressionCodeTokenOne,
                        1 => $expressionCodeTokenTwo,
                        default => throw new RuntimeException("Did not expected to be called"),
                    };
                },
            );

        $parser = new AttributeParseSpecification($expressionParseSpecification, $referenceParseSpecification);
        $attribute = $parser->parse($lexicals);

        self::assertInstanceOf(AttributeCodeToken::class, $attribute);
        self::assertEquals(
            new AttributeCodeToken(
                new ReferenceCodeToken('Foo', 'fiz'),
                [
                    $expressionCodeTokenOne,
                    $expressionCodeTokenTwo,
                ],
            ),
            $attribute,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testIsSatisfiedByMatched(): void
    {
        $atSignLexical = $this->createMock(Lexical::class);
        $atSignLexical->method('getType')->willReturn(AtSignLexical::TYPE);

        $lexicals = $this->createMock(LexicalStream::class);
        $lexicals->method('isActive')->willReturn(true);
        $lexicals->method('current')->willReturn($atSignLexical);

        $expressionParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification = $this->createMock(ParseSpecification::class);

        $parser = new AttributeParseSpecification($expressionParseSpecification, $referenceParseSpecification);

        self::assertTrue($parser->isSatisfiedBy($lexicals));
    }

    public function testIsSatisfiedByNotMatched(): void
    {
        $otherLexical = $this->createMock(Lexical::class);
        $otherLexical->method('getType')->willReturn('other');

        $lexicals = $this->createMock(LexicalStream::class);
        $lexicals->method('isActive')->willReturn(true);
        $lexicals->method('current')->willReturn($otherLexical);

        $expressionParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification = $this->createMock(ParseSpecification::class);

        $parser = new AttributeParseSpecification($expressionParseSpecification, $referenceParseSpecification);

        self::assertFalse($parser->isSatisfiedBy($lexicals));
    }
}
