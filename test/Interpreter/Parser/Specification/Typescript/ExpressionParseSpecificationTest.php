<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\InvokeCodeToken;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Value\FloatCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\Dictionary\Item;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Stream\Lexical\ArrayLexicalStream;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Token\Value\BuiltInCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\InitiateCodeToken;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Token\Value\CollectionCodeToken;
use LesCoder\Token\Expression\GroupCodeToken;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Token\Value\AnonymousFunctionCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DotLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\MinusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\ExpressionParseSpecification;
use PHPUnit\Framework\TestCase;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

#[CoversClass(ExpressionParseSpecification::class)]
class ExpressionParseSpecificationTest extends TestCase
{
    private ParseSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $referenceParseSpecification = $this->createMock(ParseSpecification::class);

        $hintParseSpecification = $this->createMock(ParseSpecification::class);

        $this->specification = new ExpressionParseSpecification(
            $referenceParseSpecification,
            $hintParseSpecification,
            ['fiz' => 'bar'],
        );
    }

    public function testParseNegativeNumberInteger(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new MinusLexical(),
                new IntegerLexical('123'),
            ],
        );

        $code = $this->specification->parse($lexicals);

        self::assertInstanceOf(IntegerCodeToken::class, $code);
        self::assertSame(-123, $code->value);
    }

    public function testParsePositiveNumberInteger(): void
    {
        $lexicals = new ArrayLexicalStream(
            [new IntegerLexical('321')],
        );

        $code = $this->specification->parse($lexicals);

        self::assertInstanceOf(IntegerCodeToken::class, $code);
        self::assertSame(321, $code->value);
    }

    public function testParsePositiveNumberFloat(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new DotLexical(),
                new IntegerLexical('123'),
            ],
        );

        $code = $this->specification->parse($lexicals);

        self::assertInstanceOf(FloatCodeToken::class, $code);
        self::assertSame(.123, $code->value);
    }

    public function testParseNegativeNumberFloat(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new MinusLexical(),
                new IntegerLexical('321'),
                new DotLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);

        self::assertInstanceOf(FloatCodeToken::class, $code);
        self::assertSame(-321., $code->value);
    }

    public function testInvalidNumber(): void
    {
        $this->expectException(UnexpectedLexical::class);

        $lexicals = new ArrayLexicalStream(
            [
                new MinusLexical(),
                new GreaterThanLexical(),
            ],
        );

        $this->specification->parse($lexicals);
    }

    public function testParseString(): void
    {
        $string = new StringLexical("foo");

        $code = $this->specification->parse(
            new ArrayLexicalStream([$string]),
        );

        self::assertInstanceOf(StringCodeToken::class, $code);
        self::assertSame('foo', $code->value);
    }

    #[DataProvider('getLabelTestValues')]
    public function testParseLabel(string $label, CodeToken $expected): void
    {
        $string = new LabelLexical($label);

        $code = $this->specification->parse(new ArrayLexicalStream([$string]));
        self::assertEquals($expected, $code);
    }

    /**
     * @return array<mixed>
     */
    public static function getLabelTestValues(): array
    {
        return [
            ['foo', new VariableCodeToken('foo')],
            ['false', BuiltInCodeToken::False],
            ['TRUE', BuiltInCodeToken::True],
            ['NuLl', BuiltInCodeToken::Null],
            ['parENT', BuiltInCodeToken::Parent],
        ];
    }

    public function testParseDict(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new CurlyBracketLeftLexical(),
                new LabelLexical('bar'),
                new ColonLexical(),
                new LabelLexical('true'),
                new CurlyBracketRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new DictionaryCodeToken(
                [
                    new Item(
                        new StringCodeToken('bar'),
                        BuiltInCodeToken::True,
                    ),
                ],
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParseDictEmpty(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new CurlyBracketLeftLexical(),
                new CurlyBracketRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new DictionaryCodeToken([]),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParseMultiItemDict(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new CurlyBracketLeftLexical(),
                new LabelLexical('bar'),
                new ColonLexical(),
                new LabelLexical('true'),
                new CommaLexical(),
                new LabelLexical('foo'),
                new ColonLexical(),
                new LabelLexical('null'),
                new CommaLexical(),
                new CurlyBracketRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new DictionaryCodeToken(
                [
                    new Item(
                        new StringCodeToken('bar'),
                        BuiltInCodeToken::True,
                    ),
                    new Item(
                        new StringCodeToken('foo'),
                        BuiltInCodeToken::Null,
                    ),
                ],
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParseCollectionEmpty(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new SquareBracketLeftLexical(),
                new SquareBracketRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new CollectionCodeToken([]),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParseCollectionMulti(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new SquareBracketLeftLexical(),
                new IntegerLexical('123'),
                new CommaLexical(),
                new StringLexical('foo'),
                new CommaLexical(),
                new SquareBracketRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new CollectionCodeToken(
                [
                    new IntegerCodeToken(123),
                    new StringCodeToken('foo'),
                ],
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParseDotAccess(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new StringLexical('foo'),
                new DotLexical(),
                new LabelLexical('bar'),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new AccessCodeToken(
                new StringCodeToken('foo'),
                new StringCodeToken('bar'),
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParseInvoke(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new StringLexical('foo'),
                new ParenthesisLeftLexical(),
                new LabelLexical('bar'),
                new CommaLexical(),
                new IntegerLexical('1'),
                new CommaLexical(),
                new ParenthesisRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new InvokeCodeToken(
                new StringCodeToken('foo'),
                [
                    new VariableCodeToken('bar'),
                    new IntegerCodeToken(1),
                ],
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParseDynamicAccess(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new StringLexical('foo'),
                new SquareBracketLeftLexical(),
                new LabelLexical('bar'),
                new SquareBracketRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new AccessCodeToken(
                new StringCodeToken('foo'),
                new VariableCodeToken('bar'),
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testParenthesisGroup(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new ParenthesisLeftLexical(),
                new LabelLexical('bar'),
                new ParenthesisRightLexical(),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new GroupCodeToken(
                new VariableCodeToken('bar'),
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testReferenceAccess(): void
    {
        $lexicals = new ArrayLexicalStream(
            [
                new LabelLexical('fiz'),
                new DotLexical(),
                new LabelLexical('bar'),
            ],
        );

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new AccessCodeToken(
                new ReferenceCodeToken('fiz', 'bar'),
                new StringCodeToken('bar'),
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testInitialization(): void
    {
        $referenceToken = $this->createMock(CodeToken::class);

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification
            ->expects(self::once())
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) use ($referenceToken) {
                    $stream->next();

                    return $referenceToken;
                },
            );

        $hintParseSpecification = $this->createMock(ParseSpecification::class);

        $specification = new ExpressionParseSpecification(
            $referenceParseSpecification,
            $hintParseSpecification,
            ['fiz' => 'bar'],
        );

        $lexicals = new ArrayLexicalStream(
            [
                new LabelLexical('new'),
                new LabelLexical('fiz'),
                new ParenthesisLeftLexical(),
                new IntegerLexical('123'),
                new ParenthesisRightLexical(),
            ],
        );

        $code = $specification->parse($lexicals);
        self::assertEquals(
            new InitiateCodeToken(
                $referenceToken,
                [new IntegerCodeToken(123)],
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testAnonymousFunctionSimple(): void
    {
        $code = <<<'TS'
() => 'bar'
TS;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new AnonymousFunctionCodeToken(
                body: [
                    new StringCodeToken('bar'),
                ]
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testAnonymousFunctionArrowDetection(): void
    {
        $code = <<<'TS'
(foo) => 'bar'
TS;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new AnonymousFunctionCodeToken(
                [new ParameterCodeToken('foo'),],
                body: [new StringCodeToken('bar')]
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testAnonymousFunctionArrowDetectionWithoutParentises(): void
    {
        $code = <<<'TS'
(foo => 'bar')
TS;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $code = $this->specification->parse($lexicals);
        self::assertEquals(
            new GroupCodeToken(
                new AnonymousFunctionCodeToken(
                    [new ParameterCodeToken('foo')],
                    body: [new StringCodeToken('bar')]
                ),
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }

    public function testAnonymousFunctionReturnsDetection(): void
    {
        $code = <<<'TS'
(foo): string => 'bar'
TS;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);

        $hintParseSpecification = $this->createMock(ParseSpecification::class);
        $hintParseSpecification
            ->expects(self::once())
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) {
                    self::assertEquals(
                        new LabelLexical('string'),
                        $stream->current(),
                    );
                    $stream->next();

                    return \LesCoder\Token\Hint\BuiltInCodeToken::String;
                },
            );

        $specification = new ExpressionParseSpecification(
            $referenceParseSpecification,
            $hintParseSpecification,
            ['fiz' => 'bar'],
        );

        $code = $specification->parse($lexicals);
        self::assertEquals(
            new AnonymousFunctionCodeToken(
                [new ParameterCodeToken('foo')],
                returns: \LesCoder\Token\Hint\BuiltInCodeToken::String,
                body: [new StringCodeToken('bar')]
            ),
            $code,
        );

        self::assertTrue($lexicals->isEnd());
    }
}
