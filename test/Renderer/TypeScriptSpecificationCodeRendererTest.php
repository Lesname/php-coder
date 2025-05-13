<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer;

use Override;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Object\ClassCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Expression\AndCodeToken;
use LesCoder\Token\Object\InitiateCodeToken;
use LesCoder\Token\Object\InterfaceCodeToken;
use LesCoder\Token\Value\CollectionCodeToken;
use LesCoder\Token\Expression\ExportCodeToken;
use LesCoder\Token\Expression\OrCodeToken;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Token\Object\InterfaceMethodCodeToken;
use LesCoder\Token\Object\InterfacePropertyCodeToken;
use LesCoder\Token\Expression\TypeDeclarationCodeToken;
use LesCoder\Renderer\TypeScriptSpecificationCodeRenderer;
use LesCoder\Token\Expression\VariableDeclarationCodeToken;
use LesCoder\Token\FileCodeToken;
use LesCoder\Token\Hint;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\InvokeCodeToken;
use LesCoder\Token\LineCodeToken;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\ReturnCodeToken;
use LesCoder\Token\Value;
use LesCoder\Token\VariableCodeToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\TypeScriptSpecificationCodeRenderer
 */
class TypeScriptSpecificationCodeRendererTest extends TestCase
{
    protected CodeRenderer $codeRenderer;
    #[Override]
    public function setUp(): void
    {
        $this->codeRenderer = new TypeScriptSpecificationCodeRenderer();
    }

    public function testRenderAttribute(): void
    {
        $attribute = new AttributeCodeToken(
            new Hint\ReferenceCodeToken('attr', 'attr'),
            [new Value\StringCodeToken('fiz')],
        );

        $expected = <<<'TYPESCRIPT'
@attr('fiz')
TYPESCRIPT;

        self::assertSame($expected, $this->codeRenderer->render($attribute));
    }

    public function testRenderInvoke(): void
    {
        $invoke = new InvokeCodeToken(
            new Hint\ReferenceCodeToken('bar', 'foo'),
            [new Value\StringCodeToken('boz')],
        );

        self::assertSame("bar('boz')", $this->codeRenderer->render($invoke));
    }

    public function testRenderVariable(): void
    {
        $variable = new VariableCodeToken('fiz');
        $renderer = $this->codeRenderer;

        self::assertSame('fiz', $renderer->render($variable));
    }

    public function testRenderReturn(): void
    {
        $variable = new VariableCodeToken('fiz');
        $return = new ReturnCodeToken($variable);

        self::assertSame('return fiz', $this->codeRenderer->render($return));
    }

    public function testRenderObjectAccess(): void
    {
        $token = new AccessCodeToken(
            new AccessCodeToken(
                new AccessCodeToken(
                    new Hint\ReferenceCodeToken('bar', 'foo'),
                    new Value\StringCodeToken('fiz'),
                ),
                new Value\StringCodeToken('_123'),
            ),
            new Value\StringCodeToken('fiz$'),
        );

        self::assertSame("bar.fiz._123.fiz$", $this->codeRenderer->render($token));
    }

    public function testRenderObjectAccessNonLabelChar(): void
    {
        $token = new AccessCodeToken(
            new Hint\ReferenceCodeToken('bar', 'foo'),
            new Value\StringCodeToken('fiz#'),
            AccessCodeToken::FLAG_NULLABLE,
        );

        self::assertSame("bar?.['fiz#']", $this->codeRenderer->render($token));
    }

    public function testRenderObjectAccessNullable(): void
    {
        $token = new AccessCodeToken(
            new Hint\ReferenceCodeToken('bar', 'foo'),
            new Value\StringCodeToken('fiz'),
            AccessCodeToken::FLAG_NULLABLE,
        );

        self::assertSame("bar?.fiz", $this->codeRenderer->render($token));
    }

    public function testRenderObjectClass(): void
    {
        $token = new ClassCodeToken(
            'Fiz',
            extends: new Hint\ReferenceCodeToken('Extends', 'ex'),
            implements: [new Hint\ReferenceCodeToken('Impl', 'impl')],
            attributes: [
                new AttributeCodeToken(
                    new Hint\ReferenceCodeToken('Attr', 'attr.from'),
                    [new Value\StringCodeToken('attr.string')]
                ),
            ],
            properties: [
                new ClassPropertyCodeToken(
                    Visibility::Public,
                    'biz',
                    new Hint\ReferenceCodeToken('bar', 'foo'),
                    attributes: [
                        new AttributeCodeToken(
                            new ReferenceCodeToken('Fiz', 'foo'),
                            [],
                        ),
                    ],
                ),
            ],
            methods: [
                new ClassMethodCodeToken(
                    Visibility::Public,
                    'far',
                    [
                        new ParameterCodeToken(
                            'arr',
                            Hint\BuiltInCodeToken::Dictionary,
                        ),
                    ],
                    Hint\BuiltInCodeToken::Integer,
                    [new LineCodeToken(new VariableCodeToken('fiz'))],
                ),
            ],
        );

        $expected = <<<'TYPESCRIPT'
@Attr('attr.string')
export class Fiz extends Extends implements Impl {
    @Fiz() public biz: bar;

    public far(arr: {}): number {
        fiz;
    }
}

TYPESCRIPT;

        self::assertSame($expected, $this->codeRenderer->render($token));
    }

    public function testObjectInitiate(): void
    {
        $token = new InitiateCodeToken(
            new Hint\ReferenceCodeToken('Bar', 'foo'),
            [new Value\IntegerCodeToken(123456)],
        );

        self::assertSame('new Bar(123_456)', $this->codeRenderer->render($token));
    }

    public function testRenderObjectInterface(): void
    {
        $token = new InterfaceCodeToken(
            'Fiz',
            extends: [new Hint\ReferenceCodeToken('Extends', 'ex')],
            attributes: [
                new AttributeCodeToken(
                    new Hint\ReferenceCodeToken('Attr', 'attr.from'),
                    [new Value\StringCodeToken('attr.string')]
                ),
            ],
            properties: [
                new InterfacePropertyCodeToken(
                    new Value\StringCodeToken('biz'),
                    new Hint\ReferenceCodeToken('bar', 'foo'),
                ),
            ],
            methods: [
                new InterfaceMethodCodeToken(
                    'far',
                    [
                        new ParameterCodeToken(
                            'arr',
                            Hint\BuiltInCodeToken::Dictionary,
                        ),
                    ],
                    Hint\BuiltInCodeToken::Integer,
                ),
            ],
        );

        $expected = <<<'TYPESCRIPT'
@Attr('attr.string')
export interface Fiz extends Extends {
    biz: bar;

    far(arr: {}): number;
}

TYPESCRIPT;

        self::assertSame($expected, $this->codeRenderer->render($token));
    }

    public function testRenderObjectClassMethod(): void
    {
        $token = new ClassMethodCodeToken(
            Visibility::Public,
            'fiz',
            [
                new ParameterCodeToken(
                    'lor',
                    new Hint\ReferenceCodeToken('Lor', 'lor'),
                ),
                new ParameterCodeToken(
                    'um',
                    Hint\BuiltInCodeToken::String,
                ),
            ],
            new Hint\ReferenceCodeToken('Foo', 'bar'),
            [new LineCodeToken(new VariableCodeToken('biz'))],
        );

        $expected = <<<'TYPESCRIPT'
public fiz(
    lor: Lor,
    um: string,
): Foo {
    biz;
}
TYPESCRIPT;

        self::assertSame($expected, $this->codeRenderer->render($token));
    }

    public function testRenderAnonymousFunction(): void
    {
        $token = new Value\AnonymousFunctionCodeToken(
            [
                new ParameterCodeToken(
                    'far',
                    new Hint\ReferenceCodeToken('Fiz', 'biz'),
                ),
            ],
            Hint\BuiltInCodeToken::Void,
            [new LineCodeToken(new VariableCodeToken('bar'))],
        );

        $expected = <<<'TYPESCRIPT'
(far: Fiz): void => {
    bar;
}
TYPESCRIPT;

        self::assertSame($expected, $this->codeRenderer->render($token));
    }

    public function testRenderAnonymousSimpleReturnFunction(): void
    {
        $token = new Value\AnonymousFunctionCodeToken(
            [
                new ParameterCodeToken(
                    'far',
                    new Hint\ReferenceCodeToken('Fiz', 'biz'),
                ),
            ],
            Hint\BuiltInCodeToken::Void,
            [new LineCodeToken(new ReturnCodeToken(new VariableCodeToken('bar')))],
        );

        $expected = <<<'TYPESCRIPT'
(far: Fiz): void => bar
TYPESCRIPT;

        self::assertSame($expected, $this->codeRenderer->render($token));
    }

    public function testRenderValueDictionary(): void
    {
        $token = new Value\DictionaryCodeToken(
            [
                new Value\Dictionary\Item(
                    new Value\StringCodeToken('foo'),
                    new VariableCodeToken('foo'),
                ),
                new Value\Dictionary\Item(
                    new Value\StringCodeToken('bar'),
                    new Value\DictionaryCodeToken(
                        [
                            new Value\Dictionary\Item(
                                new Value\StringCodeToken('baz'),
                                Value\BuiltInCodeToken::True,
                            ),
                        ],
                    ),
                ),
                new Value\Dictionary\Item(
                    new Value\StringCodeToken('biz'),
                    new Value\DictionaryCodeToken([]),
                ),
            ],
        );

        $exepected = <<<'TXT'
{
    foo,
    bar: { baz: true },
    biz: {},
}
TXT;

        self::assertSame($exepected, $this->codeRenderer->render($token));
    }

    public function testRenderExpressionAnd(): void
    {
        $left = new VariableCodeToken('bar');
        $right = new VariableCodeToken('fiz');

        $and = new AndCodeToken(
            [
                $left,
                $right,
            ],
        );

        self::assertSame('bar && fiz', $this->codeRenderer->render($and));
    }

    public function testRenderExpressionOr(): void
    {
        $left = new VariableCodeToken('bar');
        $right = new VariableCodeToken('fiz');

        $and = new OrCodeToken(
            [
                $left,
                $right,
            ],
        );

        self::assertSame('bar || fiz', $this->codeRenderer->render($and));
    }

    public function testMultiLevelInvokeChain(): void
    {
        $code = new InvokeCodeToken(
            new AccessCodeToken(
                new InvokeCodeToken(
                    new AccessCodeToken(
                        new AccessCodeToken(
                            new Value\StringCodeToken('fiz'),
                            new Value\StringCodeToken('biz'),
                        ),
                        new Value\StringCodeToken('foo'),
                    ),
                    []
                ),
                new Value\StringCodeToken('faz'),
            ),
            [
                Value\BuiltInCodeToken::Null,
                new Value\StringCodeToken('lorem'),
                new Value\StringCodeToken('ipsum'),
                new InvokeCodeToken(
                    new ReferenceCodeToken('foo'),
                    [
                        new Value\IntegerCodeToken(1),
                        new Value\IntegerCodeToken(3),
                        new InvokeCodeToken(
                            new ReferenceCodeToken('foo'),
                            [
                                new Value\IntegerCodeToken(9),
                                new Value\IntegerCodeToken(3),
                                new Value\IntegerCodeToken(1),
                            ],
                        ),
                    ],
                ),
            ],
        );

        $expected = <<<TXT
'fiz'.biz.foo().faz(
    null,
    'lorem',
    'ipsum',
    foo(
        1,
        3,
        foo(
            9,
            3,
            1,
        ),
    ),
)
TXT;

        self::assertSame($expected, $this->codeRenderer->render($code));
    }

    public function testRenderAccessChainLine(): void
    {
        $code = new InvokeCodeToken(
            new AccessCodeToken(
                new InvokeCodeToken(
                    new AccessCodeToken(
                        new AccessCodeToken(
                            new Value\StringCodeToken('fiz'),
                            new Value\StringCodeToken('biz'),
                        ),
                        new Value\StringCodeToken('foo'),
                    ),
                    []
                ),
                new Value\StringCodeToken('faz'),
            ),
            [Value\BuiltInCodeToken::Null],
        );

        $expected = <<<TXT
'fiz'.biz.foo().faz(null)
TXT;

        self::assertSame($expected, $this->codeRenderer->render($code));
    }

    public function testRenderInvokeMultipleParametersIndent(): void
    {
        $code = new InvokeCodeToken(
            new Value\StringCodeToken('fiz'),
            [
                Value\BuiltInCodeToken::Null,
                new Value\StringCodeToken('lorem'),
            ],
        );

        $expected = <<<TXT
'fiz'(null, 'lorem')
TXT;

        self::assertSame($expected, $this->codeRenderer->render($code));
    }

    public function testRenderTypeDeclaration(): void
    {
        $code = new TypeDeclarationCodeToken('fiz');

        $result = $this->codeRenderer->render($code);

        self::assertSame('type fiz', $result);
    }

    public function testRenderFile(): void
    {
        $token = new FileCodeToken(
            [
                new LineCodeToken(
                    new ExportCodeToken(
                        new Value\AssignmentCodeToken(
                            new VariableDeclarationCodeToken(true, 'far'),
                            new Value\CollectionCodeToken(
                                [new Value\StringCodeToken('baz')],
                            ),
                        ),
                    ),
                ),
                new LineCodeToken(
                    new ExportCodeToken(
                        new Value\AssignmentCodeToken(
                            new TypeDeclarationCodeToken('foo'),
                            new Hint\UnionCodeToken(
                                [
                                    new Value\StringCodeToken('biz'),
                                    new Value\StringCodeToken('fiz'),
                                ],
                            ),
                        ),
                    )
                ),
            ],
        );

        $result = $this->codeRenderer->render($token);

        $expected = <<<'TYPESCRIPT'
export const far = ['baz'];
export type foo = 'biz' | 'fiz';

TYPESCRIPT;

        self::assertSame($expected, $result);
    }
}
