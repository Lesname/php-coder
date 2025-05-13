<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter;

use LesCoder\Token\InvokeCodeToken;
use LesCoder\Token\Hint;
use LesCoder\Token\Value;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Token\Object\ClassCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Object\InitiateCodeToken;
use LesCoder\Token\Expression\GroupCodeToken;
use LesCoder\Token\Expression\ExportCodeToken;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Interpreter\TypescriptCodeInterpreter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Interpreter\TypescriptCodeInterpreter
 */
class TypescriptCodeInterpreterTest extends TestCase
{
    public function testInterpret(): void
    {
        $typescript = <<<'TS'
import { Foo } from 'bar';

export class Fiz extends Foo {
}
TS;

        $stream = new DirectStringStream($typescript);

        $interpreter = new TypescriptCodeInterpreter();
        $codeTokens = $interpreter->interpret($stream);

        $current = $codeTokens->current();

        self::assertInstanceOf(ExportCodeToken::class, $current);

        $codeTokens->next();

        self::assertTrue($codeTokens->isEnd());
    }

    public function testParseBasicClass(): void
    {
        $interpreter = new TypescriptCodeInterpreter();

        $contents = file_get_contents(__DIR__ . '/stubs/basic.component.ts');
        assert(is_string($contents));

        $stream = $interpreter->interpret(new DirectStringStream($contents));

        $template = <<<'TXT'

        {{ 'fooBar.foo.' + bar | translate }}
    
TXT;

        self::assertEquals(
            new ExportCodeToken(
                new ClassCodeToken(
                    'Test',
                    attributes: [
                        new AttributeCodeToken(
                            new Hint\ReferenceCodeToken('Component', '@angular/core'),
                            [
                                new Value\DictionaryCodeToken(
                                    [
                                        new Value\Dictionary\Item(
                                            new Value\StringCodeToken('selector'),
                                            new Value\StringCodeToken('test'),
                                        ),
                                        new Value\Dictionary\Item(
                                            new Value\StringCodeToken('changeDetection'),
                                            new AccessCodeToken(
                                                new Hint\ReferenceCodeToken(
                                                    'ChangeDetectionStrategy',
                                                    '@angular/core',
                                                ),
                                                new Value\StringCodeToken('OnPush'),
                                            ),
                                        ),
                                        new Value\Dictionary\Item(
                                            new Value\StringCodeToken('template'),
                                            new Value\StringCodeToken($template),
                                        ),
                                    ],
                                ),
                            ],
                        ),
                    ],
                    properties: [
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'bar',
                            new Hint\UnionCodeToken(
                                [
                                    new Value\StringCodeToken('biz'),
                                    new Value\StringCodeToken('fiz'),
                                    Hint\BuiltInCodeToken::Null,
                                ],
                            ),
                            Value\BuiltInCodeToken::Null,
                            0,
                            [
                                new AttributeCodeToken(
                                    new Hint\ReferenceCodeToken('Foo', 'bar'),
                                    [],
                                ),
                            ],
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Private,
                            'biz',
                            new Hint\UnionCodeToken(
                                [
                                    Hint\BuiltInCodeToken::Float,
                                    Hint\BuiltInCodeToken::Integer,
                                ],
                            ),
                        ),
                    ],
                    methods: [
                        new ClassMethodCodeToken(
                            Visibility::Protected,
                            'foo',
                            parameters: [
                                new ParameterCodeToken(
                                    'a',
                                    Hint\BuiltInCodeToken::String,
                                ),
                                new ParameterCodeToken(
                                    'c',
                                    new Hint\UnionCodeToken(
                                        [
                                            new Value\StringCodeToken('foo'),
                                            new Value\StringCodeToken('bar'),
                                        ],
                                    ),
                                    new Value\StringCodeToken('foo'),
                                ),
                                new ParameterCodeToken(
                                    'fiz',
                                    Hint\BuiltInCodeToken::Any,
                                    new Value\IntegerCodeToken(1),
                                    [
                                        new AttributeCodeToken(
                                            new Hint\ReferenceCodeToken('Bar', 'foo'),
                                            [],
                                        ),
                                    ],
                                ),
                            ],
                            returns: Hint\BuiltInCodeToken::Void,
                        ),
                    ],
                ),
            ),
            $stream->current(),
        );
    }

    public function testParseComplexClass(): void
    {
        $interpreter = new TypescriptCodeInterpreter();

        $contents = file_get_contents(__DIR__ . '/stubs/complex.component.ts');
        assert(is_string($contents));

        $stream = $interpreter->interpret(new DirectStringStream($contents));

        self::assertEquals(
            new ExportCodeToken(
                new ClassCodeToken(
                    'FizBizBar',
                    extends: new Hint\GenericCodeToken(
                        new Hint\ReferenceCodeToken('Fiz'),
                        [new Value\StringCodeToken('123')],
                    ),
                    implements: [
                        new Hint\GenericCodeToken(
                            new Hint\ReferenceCodeToken('OnInit', '@angular/core'),
                            [new Value\StringCodeToken('321')],
                        ),
                    ],
                    attributes: [
                        new AttributeCodeToken(
                            new Hint\ReferenceCodeToken('Component', '@angular/core'),
                            [
                                new Value\DictionaryCodeToken(
                                    [
                                        new Value\Dictionary\Item(
                                            new Value\StringCodeToken('changeDetection'),
                                            new AccessCodeToken(
                                                new Hint\ReferenceCodeToken(
                                                    'ChangeDetectionStrategy',
                                                    '@angular/core',
                                                ),
                                                new Value\StringCodeToken('OnPush'),
                                            ),
                                        ),
                                        new Value\Dictionary\Item(
                                            new Value\StringCodeToken('template'),
                                            new Value\StringCodeToken(''),
                                        ),
                                        new Value\Dictionary\Item(
                                            new Value\StringCodeToken('styles'),
                                            new Value\CollectionCodeToken(
                                                [
                                                    new Value\StringCodeToken(''),
                                                ],
                                            ),
                                        ),
                                    ],
                                ),
                            ],
                        ),
                    ],
                    properties: [
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'salesStatsPerPeriod$',
                            hint: new Hint\GenericCodeToken(
                                new Hint\ReferenceCodeToken('Observable', 'rxjs'),
                                [Hint\BuiltInCodeToken::Any],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'salesStatsPerChannel$',
                            hint: new Hint\GenericCodeToken(
                                new Hint\ReferenceCodeToken('Observable', 'rxjs'),
                                [Hint\BuiltInCodeToken::Any],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'salesStatsTotal$',
                            hint: new Hint\GenericCodeToken(
                                new Hint\ReferenceCodeToken('Observable', 'rxjs'),
                                [
                                    new Hint\UnionCodeToken(
                                        [
                                            Hint\BuiltInCodeToken::Float,
                                            Hint\BuiltInCodeToken::Integer,
                                        ],
                                    ),
                                ],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'foo',
                            hint: Hint\BuiltInCodeToken::String,
                            attributes: [
                                new AttributeCodeToken(
                                    new Hint\ReferenceCodeToken(
                                        'Input',
                                        '@angular/core',
                                    ),
                                ),
                            ],
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'fooReq',
                            hint: Hint\BuiltInCodeToken::String,
                            attributes: [
                                new AttributeCodeToken(
                                    new Hint\ReferenceCodeToken(
                                        'Input',
                                        '@angular/core',
                                    ),
                                    [
                                        new Value\DictionaryCodeToken(
                                            [
                                                new Value\Dictionary\Item(
                                                    new Value\StringCodeToken('required'),
                                                    Value\BuiltInCodeToken::True,
                                                ),
                                            ],
                                        ),
                                    ],
                                ),
                            ],
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'bar',
                            assigned: new InvokeCodeToken(
                                new Hint\GenericCodeToken(
                                    new Hint\ReferenceCodeToken('input', '@angular/core'),
                                    [Hint\BuiltInCodeToken::String],
                                ),
                                [],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'barReq',
                            assigned: new InvokeCodeToken(
                                new Hint\GenericCodeToken(
                                    new AccessCodeToken(
                                        new Hint\ReferenceCodeToken('input', '@angular/core'),
                                        new Value\StringCodeToken('required'),
                                    ),
                                    [Hint\BuiltInCodeToken::String],
                                ),
                                [],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'loading$',
                            assigned: new InitiateCodeToken(
                                new Hint\ReferenceCodeToken('BehaviorSubject', 'rxjs'),
                                [Value\BuiltInCodeToken::False],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'periodForm',
                            assigned: new InitiateCodeToken(
                                new Hint\ReferenceCodeToken('FormGroup', '@angular/forms'),
                                [
                                    new Value\DictionaryCodeToken(
                                        [
                                            new Value\Dictionary\Item(
                                                new Value\StringCodeToken('year'),
                                                new InitiateCodeToken(
                                                    new Hint\ReferenceCodeToken('FormControl', '@angular/forms'),
                                                    [
                                                        new InvokeCodeToken(
                                                            new AccessCodeToken(
                                                                new GroupCodeToken(
                                                                    new InitiateCodeToken(
                                                                        new Hint\ReferenceCodeToken('Date'),
                                                                        [],
                                                                    ),
                                                                ),
                                                                new Value\StringCodeToken('getFullYear'),
                                                            ),
                                                            [],
                                                        ),
                                                    ],
                                                ),
                                            ),
                                            new Value\Dictionary\Item(
                                                new Value\StringCodeToken('month'),
                                                new InitiateCodeToken(
                                                    new Hint\ReferenceCodeToken('FormControl', '@angular/forms'),
                                                    [Value\BuiltInCodeToken::Null],
                                                ),
                                            ),
                                        ],
                                    ),
                                ],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'channels',
                            hint: new Hint\GenericCodeToken(
                                Hint\BuiltInCodeToken::Collection,
                                [
                                    new Hint\UnionCodeToken(
                                        [
                                            new Value\StringCodeToken('bookshop'),
                                            new Value\StringCodeToken('boekenbank'),
                                            new Value\StringCodeToken('author'),
                                            new Value\StringCodeToken('bx'),
                                            new Value\StringCodeToken('webshop'),
                                            new Value\StringCodeToken('cb'),
                                            new Value\StringCodeToken('bol'),
                                        ],
                                    ),
                                ],
                            ),
                            assigned: new Value\CollectionCodeToken(
                                [
                                    new Value\StringCodeToken('bookshop'),
                                    new Value\StringCodeToken('boekenbank'),
                                    new Value\StringCodeToken('author'),
                                    new Value\StringCodeToken('bx'),
                                    new Value\StringCodeToken('webshop'),
                                    new Value\StringCodeToken('cb'),
                                    new Value\StringCodeToken('bol'),
                                ],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'activeEntries$',
                            assigned: new InitiateCodeToken(
                                new Hint\GenericCodeToken(
                                    new Hint\ReferenceCodeToken('BehaviorSubject', 'rxjs'),
                                    [
                                        new Hint\GenericCodeToken(
                                            Hint\BuiltInCodeToken::Collection,
                                            [
                                                new Hint\DictionaryCodeToken(
                                                    [
                                                        [
                                                            'key' => new Value\StringCodeToken('name'),
                                                            'value' => Hint\BuiltInCodeToken::String,
                                                            'required' => true,
                                                        ],
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ),
                                [new Value\CollectionCodeToken([])],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'channelsForm',
                            hint: new Hint\ReferenceCodeToken('FormGroup', '@angular/forms'),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'colorScheme$',
                            hint: new Hint\GenericCodeToken(
                                new Hint\ReferenceCodeToken('Observable', 'rxjs'),
                                [Hint\BuiltInCodeToken::Any],
                            ),
                        ),
                        new ClassPropertyCodeToken(
                            Visibility::Public,
                            'colors',
                            assigned: new Value\CollectionCodeToken(
                                [
                                    new Value\StringCodeToken('rgb(168, 56, 93)'),
                                    new Value\StringCodeToken('rgb(122, 163, 229)'),
                                    new Value\StringCodeToken('rgb(162, 126, 168)'),
                                    new Value\StringCodeToken('rgb(170, 227, 245)'),
                                    new Value\StringCodeToken('rgb(173, 205, 237)'),
                                    new Value\StringCodeToken('rgb(169, 89, 99)'),
                                    new Value\StringCodeToken('rgb(135, 150, 192)'),
                                ],
                            ),
                        ),
                    ],
                    methods: [
                        new ClassMethodCodeToken(
                            Visibility::Public,
                            'constructor',
                            parameters: [
                                new ClassPropertyCodeToken(
                                    Visibility::Private,
                                    'bsAdministrationOrderService',
                                    new Hint\ReferenceCodeToken(
                                        'BsAdministrationOrderService',
                                        '@boekscout/bs-administration',
                                    ),
                                ),
                            ],
                        ),
                        new ClassMethodCodeToken(
                            Visibility::Public,
                            'ngOnInit',
                            returns: Hint\BuiltInCodeToken::Void,
                        ),
                        new ClassMethodCodeToken(
                            Visibility::Public,
                            'onChannelEnter',
                            parameters: [
                                new ParameterCodeToken(
                                    'channel',
                                    Hint\BuiltInCodeToken::String,
                                ),
                            ],
                        ),
                        new ClassMethodCodeToken(
                            Visibility::Public,
                            'onChannelLeave',
                        ),
                    ],
                ),
            ),
            $stream->current(),
        );
    }
}
