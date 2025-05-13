<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\ClassMethodRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\Object\ClassMethodRendererSpecification
 */
class ClassMethodRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ClassMethodRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ClassMethodCodeToken(Visibility::Private, 'foo');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $parameter = new ParameterCodeToken('baz');
        $returns = $this->createMock(CodeToken::class);
        $line = $this->createMock(CodeToken::class);
        $attribute = new AttributeCodeToken(new ReferenceCodeToken('baz'), []);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(4))
            ->method('render')
            ->willReturnMap(
                [
                    [$parameter, 'Parameter'],
                    [$returns, 'returns'],
                    [$line, 'line'],
                    [$attribute, 'attribute'],
                ],
            );

            $comment = new CommentCodeToken('Comment');

        $token = new ClassMethodCodeToken(
            Visibility::Private,
            'foo',
            [$parameter],
            $returns,
            [$line],
            ClassMethodCodeToken::FLAG_OVERRIDE | ClassMethodCodeToken::FLAG_STATIC,
            [$attribute],
            $comment,
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
attribute
/** Comment */
private static override foo(Parameter): returns {
    line
}
TXT;

        self::assertSame($expected, $rendered);
    }
}
