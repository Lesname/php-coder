<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification\Typescript;

use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Hint\UnionCodeToken;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Token\Hint\GenericCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Hint\DictionaryCodeToken;
use LesCoder\Token\Hint\IntersectionCodeToken;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\HintParseSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Interpreter\Parser\Specification\Typescript\HintParseSpecification
 */
class HintParseSpecificationTest extends TestCase
{
    public function testParseDictionary(): void
    {
        $code = <<<'TYPESCRIPT'
{
    foo?: 1,
    bar: 2;
}
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification->expects(self::never())->method('isSatisfiedBy');
        $referenceParseSpecification->expects(self::never())->method('parse');

        $parser = new HintParseSpecification($referenceParseSpecification);

        $code = $parser->parse($lexicals);

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new DictionaryCodeToken(
                [
                    [
                        'key' => new StringCodeToken('foo'),
                        'value' => new IntegerCodeToken(1),
                        'required' => false,
                    ],
                    [
                        'key' => new StringCodeToken('bar'),
                        'value' => new IntegerCodeToken(2),
                        'required' => true,
                    ],
                ],
            ),
            $code,
        );
    }

    public function testParseAccessHint(): void
    {
        $code = <<<'TYPESCRIPT'
foo.bar
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification->expects(self::never())->method('isSatisfiedBy');
        $referenceParseSpecification
            ->expects(self::once())
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) {
                    $lexical = $stream->current();
                    $stream->next();

                    return new ReferenceCodeToken((string)$lexical);
                }
            );

        $parser = new HintParseSpecification($referenceParseSpecification);

        $code = $parser->parse($lexicals);

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new AccessCodeToken(
                new ReferenceCodeToken('foo'),
                new StringCodeToken('bar'),
            ),
            $code,
        );
    }

    public function testParseUnion(): void
    {
        $code = <<<'TYPESCRIPT'
true | false
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification->expects(self::never())->method('isSatisfiedBy');
        $referenceParseSpecification->expects(self::never())->method('parse');

        $parser = new HintParseSpecification($referenceParseSpecification);

        $code = $parser->parse($lexicals);

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new UnionCodeToken(
                [
                    BuiltInCodeToken::True,
                    BuiltInCodeToken::False,
                ],
            ),
            $code,
        );
    }

    public function testParseIntersection(): void
    {
        $code = <<<'TYPESCRIPT'
true & false
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification->expects(self::never())->method('isSatisfiedBy');
        $referenceParseSpecification->expects(self::never())->method('parse');

        $parser = new HintParseSpecification($referenceParseSpecification);

        $code = $parser->parse($lexicals);

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new IntersectionCodeToken(
                [
                    BuiltInCodeToken::True,
                    BuiltInCodeToken::False,
                ],
            ),
            $code,
        );
    }

    public function testParseGeneric(): void
    {
        $code = <<<'TYPESCRIPT'
Array<string>
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification->expects(self::never())->method('isSatisfiedBy');
        $referenceParseSpecification->expects(self::never())->method('parse');

        $parser = new HintParseSpecification($referenceParseSpecification);

        $code = $parser->parse($lexicals);

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new GenericCodeToken(
                BuiltInCodeToken::Collection,
                [BuiltInCodeToken::String],
            ),
            $code,
        );
    }

    public function testParseCollection(): void
    {
        $code = <<<'TYPESCRIPT'
string[]
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification->expects(self::never())->method('isSatisfiedBy');
        $referenceParseSpecification->expects(self::never())->method('parse');

        $parser = new HintParseSpecification($referenceParseSpecification);

        $code = $parser->parse($lexicals);

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new GenericCodeToken(
                BuiltInCodeToken::Collection,
                [BuiltInCodeToken::String],
            ),
            $code,
        );
    }
}
