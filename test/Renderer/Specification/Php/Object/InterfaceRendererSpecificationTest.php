<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\InterfaceCodeToken;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Token\Object\InterfaceMethodCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\InterfaceRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Object\InterfaceRendererSpecification
 */
class InterfaceRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new InterfaceRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new InterfaceCodeToken('foo');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $extends = $this->createMock(CodeToken::class);
        $attribute = new AttributeCodeToken(new ReferenceCodeToken('baz'), []);
        $method = new InterfaceMethodCodeToken('method');
        $generic = new GenericParameterCodeToken($this->createMock(CodeToken::class));

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$extends, 'extended'],
                    [$attribute, 'attribute'],
                    [$method, 'method'],
                ],
            );

        $token = new InterfaceCodeToken(
            'Foo',
            [$extends],
            [$attribute],
            [],
            [$method],
            [$generic],
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
attribute
interface Foo extends extended
{
    method
}

TXT;

        self::assertSame($expected, $rendered);
    }
}
