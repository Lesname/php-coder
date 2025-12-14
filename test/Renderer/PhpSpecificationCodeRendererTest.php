<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer;

use Override;
use LesCoder\Token\FileCodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Block\IfCodeToken;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Object\EnumCodeToken;
use LesCoder\Token\Object\ClassCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Expression\AndCodeToken;
use LesCoder\Token\Expression\OrCodeToken;
use LesCoder\Token\Hint;
use LesCoder\Token\InvokeCodeToken;
use LesCoder\Token\LineCodeToken;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\ReturnCodeToken;
use LesCoder\Token\Value;
use LesCoder\Token\VariableCodeToken;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\Object\InitiateCodeToken;
use LesCoder\Token\Object\NamespaceCodeToken;
use LesCoder\Token\Object\InterfaceCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Renderer\PhpSpecificationCodeRenderer;
use LesCoder\Token\Object\InterfaceMethodCodeToken;

#[CoversClass(PhpSpecificationCodeRenderer::class)]
class PhpSpecificationCodeRendererTest extends TestCase
{
    protected CodeRenderer $codeRenderer;

    #[Override]
    public function setUp(): void
    {
        $this->codeRenderer = new PhpSpecificationCodeRenderer();
    }

    public function testRenderAttribute(): void
    {
        $attribute = new AttributeCodeToken(
            new Hint\ReferenceCodeToken('attr', 'attr'),
            [new Value\StringCodeToken('fiz')],
        );

        $expected = <<<'PHP'
#[attr('fiz')]
PHP;

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

    public function testRenderFile(): void
    {
        $file = new FileCodeToken(
            [new VariableCodeToken('fiz')],
        );

        $expected = <<<'PHP'
<?php

declare(strict_types=1);

$fiz

PHP;

        self::assertSame($expected, $this->codeRenderer->render($file));
    }

    public function testRenderVariable(): void
    {
        $variable = new VariableCodeToken('fiz');
        $renderer = $this->codeRenderer;

        self::assertSame('$fiz', $renderer->render($variable));
    }

    public function testRenderIf(): void
    {
        $token = new IfCodeToken(
            new VariableCodeToken('bar'),
            [
                new LineCodeToken(
                    new Value\AssignmentCodeToken(
                        new VariableCodeToken('bar'),
                        new Value\IntegerCodeToken(1),
                    ),
                ),
                new LineCodeToken(
                    new Value\AssignmentCodeToken(
                        new VariableCodeToken('biz'),
                        new Value\IntegerCodeToken(2),
                    ),
                ),
                new IfCodeToken(
                    new Value\IntegerCodeToken(5),
                    [
                        new LineCodeToken(
                            new InvokeCodeToken(
                                new Hint\ReferenceCodeToken('fiz'),
                                [new Value\IntegerCodeToken(9)],
                            ),
                        ),
                    ],
                ),
                new LineCodeToken(
                    new Value\AssignmentCodeToken(
                        new VariableCodeToken('foo'),
                        new Value\IntegerCodeToken(3),
                    ),
                ),
                new LineCodeToken(
                    new Value\AssignmentCodeToken(
                        new VariableCodeToken('fiz'),
                        new Value\IntegerCodeToken(4),
                    ),
                ),
            ],
            [
                new LineCodeToken(
                    new InvokeCodeToken(
                        new Hint\ReferenceCodeToken('var_dump'),
                        [new Value\IntegerCodeToken(9)],
                    ),
                ),
            ],
        );

        $rendered = $this->codeRenderer->render($token);

        $expect = <<<'PHP'
if ($bar) {
    $bar = 1;
    $biz = 2;

    if (5) {
        fiz(9);
    }

    $foo = 3;
    $fiz = 4;
} else {
    var_dump(9);
}
PHP;

        self::assertSame($expect, $rendered);
    }

    public function testRenderReturn(): void
    {
        $variable = new VariableCodeToken('fiz');
        $return = new ReturnCodeToken($variable);

        self::assertSame('return $fiz', $this->codeRenderer->render($return));
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

        self::assertSame('bar->fiz->_123->fiz$', $this->codeRenderer->render($token));
    }

    public function testRenderObjectAccessNonLabelChar(): void
    {
        $token = new AccessCodeToken(
            new Hint\ReferenceCodeToken('bar', 'foo'),
            new Value\StringCodeToken('fiz#'),
            AccessCodeToken::FLAG_NULLABLE,
        );

        self::assertSame("bar?->{'fiz#'}", $this->codeRenderer->render($token));
    }

    public function testRenderObjectAccessNullable(): void
    {
        $token = new AccessCodeToken(
            new Hint\ReferenceCodeToken('bar', 'foo'),
            new Value\StringCodeToken('fiz'),
            AccessCodeToken::FLAG_NULLABLE,
        );

        self::assertSame("bar?->fiz", $this->codeRenderer->render($token));
    }

    public function testRenderObjectClass(): void
    {
        $token = new ClassCodeToken(
            'Fiz',
            extends: new Hint\ReferenceCodeToken('Extends', 'ex'),
            implements: [new Hint\ReferenceCodeToken('Impl', 'impl')],
            attributes: [
                new AttributeCodeToken(
                    new Hint\ReferenceCodeToken('Attr', 'attr\from'),
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
                            new Hint\ReferenceCodeToken('Fiz', 'foo'),
                            [],
                        ),
                    ],
                    comment: new CommentCodeToken('@deprecated'),
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
            comment: new CommentCodeToken('@foo'),
        );

        $expected = <<<'PHP'
#[Attr('attr.string')]
/** @foo */
class Fiz extends Extends implements Impl
{
    /** @deprecated */
    #[Fiz] public bar $biz;

    public function far(array $arr): int
    {
        $fiz;
    }
}

PHP;

        self::assertSame($expected, $this->codeRenderer->render($token));
    }

    public function testObjectEnum(): void
    {
        $token = new EnumCodeToken(
            'Foo',
            [
                'Fiz' => new Value\StringCodeToken('bar'),
                'Biz/Bar',
                'Biz',
            ],
            Hint\BuiltInCodeToken::String,
            [
                new Hint\ReferenceCodeToken(
                    'Impl',
                    'Impl',
                ),
            ],
            [
                new Hint\ReferenceCodeToken(
                    'Uses',
                    'Uses',
                ),
            ],
            new CommentCodeToken('foo'),
        );

        $expected = <<<'PHP'
/** foo */
enum Foo: string implements Impl
{
    use Uses;

    case Fiz = 'bar';
    case BizBar;
    case Biz;
}
PHP;

        self::assertSame($expected, $this->codeRenderer->render($token));
        self::assertSame(
            [
                'Impl' => 'Impl',
                'Uses' => 'Uses',
            ],
            $token->getImports(),
        );
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
                    new Hint\ReferenceCodeToken('Attr', 'attr\from'),
                    [new Value\StringCodeToken('attr.string')]
                ),
            ],
            properties: [],
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

        $expected = <<<'PHP'
#[Attr('attr.string')]
interface Fiz extends Extends
{
    public function far(array $arr): int;
}

PHP;

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

        $expected = <<<'PHP'
public function fiz(
    Lor $lor,
    string $um,
): Foo {
    $biz;
}
PHP;

        self::assertSame($expected, $this->codeRenderer->render($token));
    }

    public function testRenderObjectNamespace(): void
    {
        $token = new VariableCodeToken('foo');

        $namespace = new NamespaceCodeToken(
            'Biz',
            [$token],
        );

        $expected = <<<'PHP'
namespace Biz;

$foo

PHP;

        self::assertSame($expected, $this->codeRenderer->render($namespace));
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

        $expected = <<<'PHP'
function (Fiz $far): void => {
    $bar;
}
PHP;

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
[
    'foo' => $foo,
    'bar' => ['baz' => true],
    'biz' => [],
]
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

        self::assertSame('$bar && $fiz', $this->codeRenderer->render($and));
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

        self::assertSame('$bar || $fiz', $this->codeRenderer->render($and));
    }

    public function testRenderAccessChainFlat(): void
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
                new Value\StringCodeToken('lorem')
            ],
        );

        $expected = <<<TXT
'fiz'->biz->foo()->faz(null, 'lorem')
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
'fiz'->biz->foo()->faz(null)
TXT;

        self::assertSame($expected, $this->codeRenderer->render($code));
    }

    public function testRenderInvokeMultipleParametersIndent(): void
    {
        $code = new InvokeCodeToken(
            new Hint\ReferenceCodeToken('fiz'),
            [
                Value\BuiltInCodeToken::Null,
                new Value\StringCodeToken('lorem')
            ],
        );

        $expected = <<<TXT
fiz(null, 'lorem')
TXT;

        self::assertSame($expected, $this->codeRenderer->render($code));
    }
}
