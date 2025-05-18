<?php
declare(strict_types=1);

namespace Interpreter\Parser\Angular;

use PHPUnit\Framework\TestCase;
use LesCoder\Token\InvokeCodeToken;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Token\Value\FloatCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\Dictionary\Item;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Token\Value\BuiltInCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Expression\OrCodeToken;
use LesCoder\Token\Expression\MathOperator;
use LesCoder\Token\Expression\AndCodeToken;
use LesCoder\Token\Expression\NotCodeToken;
use LesCoder\Token\Value\CollectionCodeToken;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Token\Expression\GroupCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Expression\FilterCodeToken;
use LesCoder\Token\Expression\TernaryCodeToken;
use LesCoder\Stream\Lexical\ArrayLexicalStream;
use LesCoder\Token\Expression\ComparisonOperator;
use LesCoder\Token\Expression\ComparisonCodeToken;
use LesCoder\Token\Expression\CoalescingCodeToken;
use LesCoder\Token\Expression\CalculationCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DotLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\OrLexical;
use LesCoder\Interpreter\Parser\Angular\ExpressionCodeParser;
use LesCoder\Interpreter\Lexer\Lexical\Character\PlusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\AndLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PipeLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\MinusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\AsteriskLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\CoalescingLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ExclamationLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\SameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

#[CoversClass(ExpressionCodeParser::class)]
class ExpressionCodeParserTest extends TestCase
{
    public function testLexVariable(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new LabelLexical('bar'),
            ],
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new VariableCodeToken('bar'),
            $codeStream->current(),
        );
    }

    public function testLexInvoke(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new LabelLexical('bar'),
                new ParenthesisLeftLexical(),
                new LabelLexical('biz'),
                new ParenthesisRightLexical(),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new InvokeCodeToken(
                new VariableCodeToken('bar'),
                [new VariableCodeToken('biz')],
            ),
            $codeStream->current(),
        );
    }

    public function testLexString(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new StringLexical("fizbizz"),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new StringCodeToken('fizbizz'),
            $codeStream->current(),
        );
    }

    public function testLexNumberInteger(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new MinusLexical(),
                new IntegerLexical('123'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new IntegerCodeToken(-123),
            $codeStream->current(),
        );
    }

    public function testLexNumberFloat(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new IntegerLexical('123'),
                new DotLexical(),
                new IntegerLexical('321'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new FloatCodeToken(123.321),
            $codeStream->current(),
        );
    }

    public function testLexList(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new SquareBracketLeftLexical(),
                new StringLexical('foo'),
                new CommaLexical(),
                new SquareBracketLeftLexical(),
                new IntegerLexical('123'),
                new SquareBracketRightLexical(),
                new CommaLexical(),
                new LabelLexical('false'),
                new SquareBracketRightLexical(),
            ],
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new CollectionCodeToken(
                [
                    new StringCodeToken('foo'),
                    new CollectionCodeToken(
                        [new IntegerCodeToken(123)],
                    ),
                    BuiltInCodeToken::False,
                ],
            ),
            $codeStream->current(),
        );
    }

    public function testLexDict(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new CurlyBracketLeftLexical(),
                new LabelLexical('foo'),
                new ColonLexical(),
                new StringLexical('bar'),
                new CommaLexical(),

                new StringLexical('biz'),
                new ColonLexical(),
                new CurlyBracketLeftLexical(),
                new LabelLexical('fiz'),
                new ColonLexical(),
                new IntegerLexical('123'),
                new CommaLexical(),
                new CurlyBracketRightLexical(),
                new CommaLexical(),

                new LabelLexical('bar'),
                new ColonLexical(),
                new LabelLexical('true'),
                new CurlyBracketRightLexical(),
            ],
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new DictionaryCodeToken(
                [
                    new Item(
                        new StringCodeToken('foo'),
                        new StringCodeToken('bar'),
                    ),
                    new Item(
                        new StringCodeToken('biz'),
                        new DictionaryCodeToken(
                            [
                                new Item(
                                    new StringCodeToken('fiz'),
                                    new IntegerCodeToken(123),
                                ),
                            ],
                        ),
                    ),
                    new Item(
                        new StringCodeToken('bar'),
                        BuiltInCodeToken::True,
                    ),
                ],
            ),
            $codeStream->current(),
        );
    }

    public function testLexGroup(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new ParenthesisLeftLexical(),
                new LabelLexical('bar'),
                new ParenthesisRightLexical(),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new GroupCodeToken(new VariableCodeToken('bar')),
            $codeStream->current(),
        );
    }

    public function testLexMath(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new ParenthesisLeftLexical(),
                new ParenthesisLeftLexical(),
                new LabelLexical('bar'),
                new WhitespaceLexical(' '),
                new MinusLexical(),
                new MinusLexical(),
                new IntegerLexical('1'),
                new ParenthesisRightLexical(),
                new WhitespaceLexical(' '),
                new PlusLexical(),
                new WhitespaceLexical(' '),
                new ParenthesisLeftLexical(),
                new IntegerLexical('2'),
                new WhitespaceLexical(' '),
                new AsteriskLexical(),
                new WhitespaceLexical(' '),
                new DotLexical(),
                new IntegerLexical('3'),
                new ParenthesisRightLexical(),
                new ParenthesisRightLexical(),
                new WhitespaceLexical(' '),
                new MinusLexical(),
                new WhitespaceLexical(' '),
                new ParenthesisLeftLexical(),
                new IntegerLexical('6'),
                new WhitespaceLexical(' '),
                new ForwardSlashLexical(),
                new WhitespaceLexical(' '),
                new IntegerLexical('2'),
                new ParenthesisRightLexical(),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new CalculationCodeToken(
                new GroupCodeToken(
                    new CalculationCodeToken(
                        new GroupCodeToken(
                            new CalculationCodeToken(
                                new VariableCodeToken('bar'),
                                new IntegerCodeToken(-1),
                                MathOperator::Subtract,
                            )
                        ),
                        new GroupCodeToken(
                            new CalculationCodeToken(
                                new IntegerCodeToken(2),
                                new FloatCodeToken(.3),
                                MathOperator::Multiply,
                            ),
                        ),
                        MathOperator::Addition,
                    ),
                ),
                new GroupCodeToken(
                    new CalculationCodeToken(
                        new IntegerCodeToken(6),
                        new IntegerCodeToken(2),
                        MathOperator::Divide,
                    ),
                ),
                MathOperator::Subtract
            ),
            $codeStream->current(),
        );
    }

    public function testNestedAccess(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new LabelLexical('bar'),
                new ParenthesisLeftLexical(),
                new ParenthesisRightLexical(),
                new DotLexical(),
                new LabelLexical('biz'),
                new DotLexical(),
                new LabelLexical('foo'),
                new SquareBracketLeftLexical(),
                new StringLexical('bar'),
                new SquareBracketRightLexical(),
                new SquareBracketLeftLexical(),
                new IntegerLexical('1'),
                new SquareBracketRightLexical(),
                new QuestionMarkLexical(),
                new DotLexical(),
                new LabelLexical('fiz'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new AccessCodeToken(
                new AccessCodeToken(
                    new AccessCodeToken(
                        new AccessCodeToken(
                            new AccessCodeToken(
                                new InvokeCodeToken(
                                    new VariableCodeToken('bar'),
                                    [],
                                ),
                                new StringCodeToken('biz'),
                            ),
                            new StringCodeToken('foo'),
                        ),
                        new StringCodeToken('bar'),
                    ),
                    new IntegerCodeToken(1),
                ),
                new StringCodeToken('fiz'),
                AccessCodeToken::FLAG_NULLABLE,
            ),
            $codeStream->current(),
        );
    }

    public function testChain(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new LabelLexical('bar'),
                new WhitespaceLexical(' '),
                new AndLexical('&&'),
                new WhitespaceLexical(' '),
                new ParenthesisLeftLexical(),
                new LabelLexical('foo'),
                new WhitespaceLexical(' '),
                new OrLexical('||'),
                new WhitespaceLexical(' '),
                new LabelLexical('biz'),
                new OrLexical('||'),
                new LabelLexical('bar'),
                new ParenthesisRightLexical(),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new AndCodeToken(
                [
                    new VariableCodeToken('bar'),
                    new GroupCodeToken(
                        new OrCodeToken(
                            [
                                new VariableCodeToken('foo'),
                                new VariableCodeToken('biz'),
                                new VariableCodeToken('bar'),
                            ],
                        ),
                    ),
                ],
            ),
            $codeStream->current(),
        );
    }

    public function testCoalesce(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new LabelLexical('bar'),
                new WhitespaceLexical(' '),
                new CoalescingLexical('??'),
                new WhitespaceLexical(' '),
                new LabelLexical('foo'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new CoalescingCodeToken(
                [
                    new VariableCodeToken('bar'),
                    new VariableCodeToken('foo'),
                ],
            ),
            $codeStream->current(),
        );
    }

    public function testParseFilter(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new LabelLexical('bar'),
                new WhitespaceLexical(' '),
                new PipeLexical(),
                new WhitespaceLexical(' '),
                new LabelLexical('fiz'),
                new ColonLexical(),
                new LabelLexical('biz'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new FilterCodeToken(
                'fiz',
                new VariableCodeToken('bar'),
                [new VariableCodeToken('biz')],
            ),
            $codeStream->current(),
        );
    }

    public function testTernary(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new LabelLexical('foo'),
                new WhitespaceLexical(' '),
                new QuestionMarkLexical(),
                new WhitespaceLexical(' '),
                new LabelLexical('bar'),
                new WhitespaceLexical(' '),
                new ColonLexical(),
                new WhitespaceLexical(' '),
                new LabelLexical('baz'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new TernaryCodeToken(
                new VariableCodeToken('foo'),
                new VariableCodeToken('bar'),
                new VariableCodeToken('baz'),
            ),
            $codeStream->current(),
        );
    }

    public function testAdditionAccess(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new StringLexical('foo'),
                new WhitespaceLexical(' '),
                new PlusLexical(),
                new WhitespaceLexical(' '),
                new LabelLexical('fiz'),
                new DotLexical(),
                new LabelLexical('biz'),
                new WhitespaceLexical(' '),
                new PipeLexical(),
                new WhitespaceLexical(' '),
                new LabelLexical('translate'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new FilterCodeToken(
                'translate',
                new CalculationCodeToken(
                    new StringCodeToken('foo'),
                    new AccessCodeToken(
                        new VariableCodeToken('fiz'),
                        new StringCodeToken('biz'),
                    ),
                    MathOperator::Addition,
                )
            ),
            $codeStream->current(),
        );
    }

    public function testCompareTernary(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new StringLexical('foo'),
                new WhitespaceLexical(' '),
                new SameLexical('==='),
                new WhitespaceLexical(' '),
                new StringLexical('bar'),
                new WhitespaceLexical(' '),
                new QuestionMarkLexical(),
                new WhitespaceLexical(' '),
                new IntegerLexical('1'),
                new WhitespaceLexical(' '),
                new ColonLexical(),
                new WhitespaceLexical(' '),
                new IntegerLexical('2'),
            ]
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new TernaryCodeToken(
                new ComparisonCodeToken(
                    new StringCodeToken('foo'),
                    new StringCodeToken('bar'),
                    ComparisonOperator::Identical,
                ),
                new IntegerCodeToken(1),
                new IntegerCodeToken(2),
            ),
            $codeStream->current(),
        );
    }

    public function testGroupNestedInvoke(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new ParenthesisLeftLexical(),
                new LabelLexical('foo'),
                new DotLexical(),
                new LabelLexical('bar'),
                new ParenthesisLeftLexical(),
                new IntegerLexical('1'),
                new PlusLexical(),
                new IntegerLexical('2'),
                new ParenthesisRightLexical(),
                new ParenthesisRightLexical(),
            ],
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new GroupCodeToken(
                new InvokeCodeToken(
                    new AccessCodeToken(
                        new VariableCodeToken('foo'),
                        new StringCodeToken('bar'),
                    ),
                    [
                        new CalculationCodeToken(
                            new IntegerCodeToken(1),
                            new IntegerCodeToken(2),
                            MathOperator::Addition,
                        ),
                    ],
                ),
            ),
            $codeStream->current(),
        );
    }

    public function testNot(): void
    {
        $parser = new ExpressionCodeParser();
        $lexicalStream = new ArrayLexicalStream(
            [
                new ExclamationLexical(),
                new LabelLexical('foo'),
                new DotLexical(),
                new LabelLexical('bar'),
                new AndLexical('&&'),
                new ExclamationLexical(),
                new ParenthesisLeftLexical(),
                new IntegerLexical('1'),
                new ParenthesisRightLexical(),
            ],
        );

        $codeStream = $parser->parse($lexicalStream, null);

        self::assertEquals(
            new AndCodeToken(
                [
                    new NotCodeToken(
                        new AccessCodeToken(
                            new VariableCodeToken('foo'),
                            new StringCodeToken('bar'),
                        ),
                    ),
                    new NotCodeToken(
                        new GroupCodeToken(
                            new IntegerCodeToken(1),
                        )
                    )
                ],
            ),
            $codeStream->current(),
        );
    }
}
