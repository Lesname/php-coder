<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Angular;

use LesCoder\Token\TextCodeToken;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Token\Block\IfCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Block\SwitchCodeToken;
use LesCoder\Token\Block\Switch\CaseItem;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Token\Expression\GroupCodeToken;
use LesCoder\Token\Value\AssignmentCodeToken;
use LesCoder\Token\Expression\FilterCodeToken;
use LesCoder\Token\Block\Angular\ForCodeToken;
use LesCoder\Token\Element\VoidElementCodeToken;
use LesCoder\Token\Block\Angular\For\Expression;
use LesCoder\Token\Expression\ComparisonOperator;
use LesCoder\Token\Expression\ComparisonCodeToken;
use LesCoder\Token\Element\NonVoidElementCodeToken;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Angular\TemplateCodeLexer;
use LesCoder\Interpreter\Parser\Angular\TemplateCodeParser;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateCodeParser::class)]
class TemplateCodeParserTest extends TestCase
{
    public function testSimple(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/simple.html');
        assert(is_string($contents));

        $lexer = new TemplateCodeLexer();
        $stringStream = new DirectStringStream($contents);

        $lexicals = $lexer->tokenize($stringStream);

        $parser = new TemplateCodeParser();
        $codeTokenStream = $parser->parse($lexicals, null);

        $expected = [
            new TextCodeToken('Foo'),
            new NonVoidElementCodeToken(
                'div',
                [],
                [
                    new VoidElementCodeToken(
                        'INPUT',
                        [
                            'type' => new TextCodeToken('text'),
                            'class' => new TextCodeToken('one'),
                            'fiz' => new TextCodeToken('biz'),
                            'bar' => new TextCodeToken('baz'),
                        ],
                    ),
                    new VoidElementCodeToken(
                        'input',
                        [
                            'type' => new TextCodeToken('text'),
                            'class' => new TextCodeToken('two'),
                        ],
                    ),
                    new TextCodeToken('Bar'),
                    new NonVoidElementCodeToken(
                        'form',
                        [
                            'action' => new TextCodeToken(''),
                            '*ngIf' => new TextCodeToken('foo'),
                        ],
                        [
                            new NonVoidElementCodeToken(
                                'fieldset',
                                [],
                                [
                                    new NonVoidElementCodeToken(
                                        'legend',
                                        [
                                            '[title]' => new FilterCodeToken(
                                                'translate',
                                                new StringCodeToken('test'),
                                            ),
                                        ],
                                        [new TextCodeToken('Fiz')],
                                    ),
                                ],
                            ),
                        ],
                    ),
                    new NonVoidElementCodeToken(
                        'button',
                        [
                            'type' => new TextCodeToken('button'),
                            'disabled' => new TextCodeToken(''),
                            'foo' => new TextCodeToken(''),
                            '[class]' => new VariableCodeToken('button'),
                            '(click)' => new TextCodeToken('foo()'),
                        ],
                        [
                            new FilterCodeToken(
                                'translate',
                                new StringCodeToken('submit'),
                            ),
                        ],
                    ),
                ],
            ),
            new NonVoidElementCodeToken(
                'ng-container',
                ['*ngFor' => new TextCodeToken('let foo in bar')],
                [
                    new FilterCodeToken(
                        'biz',
                        new StringCodeToken('fiz'),
                    ),
                ]
            ),
        ];

        foreach ($expected as $i => $item) {
            self::assertEquals($item, $codeTokenStream->current(), "on {$i}");
            $codeTokenStream->next();
        }

        self::assertTrue($codeTokenStream->isEnd());
    }

    public function testFlowControlFor(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/flow-control-for.html');
        assert(is_string($contents));

        $lexer = new TemplateCodeLexer();
        $stringStream = new DirectStringStream($contents);

        $lexicals = $lexer->tokenize($stringStream);

        $parser = new TemplateCodeParser();
        $codeTokenStream = $parser->parse($lexicals, null);

        $expected = [
            new ForCodeToken(
                new Expression(
                    new VariableCodeToken('items'),
                    'item',
                    new AccessCodeToken(
                        new VariableCodeToken('item'),
                        new StringCodeToken('id'),
                    ),
                    [
                        '$index' => 'i',
                        '$first' => 'isFirst',
                    ],
                ),
                [
                    new AccessCodeToken(
                        new VariableCodeToken('item'),
                        new StringCodeToken('name'),
                    ),
                ],
            ),
            new NonVoidElementCodeToken(
                'ul',
                [],
                [
                    new ForCodeToken(
                        new Expression(
                            new VariableCodeToken('items'),
                            'item',
                            new AccessCodeToken(
                                new VariableCodeToken('item'),
                                new StringCodeToken('name'),
                            ),
                            ['$index' => 'i'],
                        ),
                        [
                            new NonVoidElementCodeToken(
                                'li',
                                [],
                                [
                                    new AccessCodeToken(
                                        new VariableCodeToken('item'),
                                        new StringCodeToken('name'),
                                    ),
                                ],
                            ),
                        ],
                        [
                            new NonVoidElementCodeToken(
                                'li',
                                [],
                                [new TextCodeToken('There are no items.')],
                            ),
                        ],
                    ),
                ],
            ),
        ];

        foreach ($expected as $i => $item) {
            self::assertEquals($item, $codeTokenStream->current(), "on {$i}");
            $codeTokenStream->next();
        }

        self::assertTrue($codeTokenStream->isEnd());
    }

    public function testFlowControlIf(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/flow-control-if.html');
        assert(is_string($contents));

        $lexer = new TemplateCodeLexer();
        $stringStream = new DirectStringStream($contents);

        $lexicals = $lexer->tokenize($stringStream);

        $parser = new TemplateCodeParser();
        $codeTokenStream = $parser->parse($lexicals, null);

        $expected = [
            new IfCodeToken(
                new ComparisonCodeToken(
                    new VariableCodeToken('a'),
                    new VariableCodeToken('b'),
                    ComparisonOperator::GreaterThan,
                ),
                [
                    new VariableCodeToken('a'),
                    new TextCodeToken('is greater than'),
                    new VariableCodeToken('b'),
                ],
            ),
            new NonVoidElementCodeToken(
                'div',
                [],
                [
                    new IfCodeToken(
                        new ComparisonCodeToken(
                            new VariableCodeToken('a'),
                            new VariableCodeToken('b'),
                            ComparisonOperator::GreaterThan,
                        ),
                        [
                            new VariableCodeToken('a'),
                            new TextCodeToken('is greater than'),
                            new VariableCodeToken('b'),
                        ],
                        [
                            new IfCodeToken(
                                new ComparisonCodeToken(
                                    new VariableCodeToken('b'),
                                    new VariableCodeToken('a'),
                                    ComparisonOperator::GreaterThan,
                                ),
                                [
                                    new NonVoidElementCodeToken(
                                        'u',
                                        [],
                                        [
                                            new VariableCodeToken('a'),
                                            new TextCodeToken('is less than'),
                                            new VariableCodeToken('b'),
                                        ],
                                    ),
                                ],
                                [
                                    new IfCodeToken(
                                        new ComparisonCodeToken(
                                            new  VariableCodeToken('c'),
                                            new VariableCodeToken('b'),
                                            ComparisonOperator::Identical,
                                        ),
                                        [new VoidElementCodeToken('input', [])],
                                        [
                                            new IfCodeToken(
                                                new ComparisonCodeToken(
                                                    new VariableCodeToken('a'),
                                                    new GroupCodeToken(new VariableCodeToken('c')),
                                                    ComparisonOperator::Identical,
                                                ),
                                                [],
                                                [
                                                    new NonVoidElementCodeToken(
                                                        'strong',
                                                        [],
                                                        [
                                                            new VariableCodeToken('a'),
                                                            new TextCodeToken('is equal to'),
                                                            new VariableCodeToken('b'),
                                                        ],
                                                    ),
                                                ],
                                            ),
                                        ],
                                    ),
                                ],
                            ),
                        ],
                    ),
                ],
            ),
            new IfCodeToken(
                new AssignmentCodeToken(
                    new VariableCodeToken('users'),
                    new FilterCodeToken(
                        'async',
                        new VariableCodeToken('users$'),
                        [],
                    ),
                ),
                [
                    new IfCodeToken(
                        new ComparisonCodeToken(
                            new AccessCodeToken(
                                new VariableCodeToken('users'),
                                new StringCodeToken('length'),
                            ),
                            new IntegerCodeToken(0),
                            ComparisonOperator::GreaterThan,
                        ),
                        [
                            new AccessCodeToken(
                                new VariableCodeToken('users'),
                                new StringCodeToken('length'),
                            ),
                        ],
                        [
                            new TextCodeToken('none'),
                        ],
                    ),
                ],
            )
        ];

        foreach ($expected as $i => $item) {
            self::assertEquals($item, $codeTokenStream->current(), "on {$i}");
            $codeTokenStream->next();
        }

        self::assertTrue($codeTokenStream->isEnd());
    }

    public function testFlowControlSwitch(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/flow-control-switch.html');
        assert(is_string($contents));

        $lexer = new TemplateCodeLexer();
        $stringStream = new DirectStringStream($contents);

        $lexicals = $lexer->tokenize($stringStream);

        $parser = new TemplateCodeParser();
        $codeTokenStream = $parser->parse($lexicals, null);

        $expected = [
            new SwitchCodeToken(
                new VariableCodeToken('condition'),
                [
                    new CaseItem(
                        new VariableCodeToken('caseA'),
                        [new TextCodeToken('Case A.')],
                    ),
                    new CaseItem(
                        new VariableCodeToken('caseB'),
                        [new TextCodeToken('Case B.')],
                    )
                ],
                [new TextCodeToken('Default case.')],
            )
        ];

        foreach ($expected as $i => $item) {
            self::assertEquals($item, $codeTokenStream->current(), "on {$i}");
            $codeTokenStream->next();
        }

        self::assertTrue($codeTokenStream->isEnd());
    }

    public function testFlowControlLet(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/flow-control-let.html');
        assert(is_string($contents));

        $lexer = new TemplateCodeLexer();
        $stringStream = new DirectStringStream($contents);

        $lexicals = $lexer->tokenize($stringStream);

        $parser = new TemplateCodeParser();
        $codeTokenStream = $parser->parse($lexicals, null);

        $expected = [
            new IfCodeToken(
                new VariableCodeToken('foo'),
                [
                    new NonVoidElementCodeToken(
                        'div',
                        [],
                        [
                            new AssignmentCodeToken(
                                new VariableCodeToken('foo'),
                                new StringCodeToken('bar'),
                            ),
                        ],
                    ),
                ],
            ),
        ];

        foreach ($expected as $i => $item) {
            self::assertEquals($item, $codeTokenStream->current(), "on {$i}");
            $codeTokenStream->next();
        }

        self::assertTrue($codeTokenStream->isEnd());
    }
}
