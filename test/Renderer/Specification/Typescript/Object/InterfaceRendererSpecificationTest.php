<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\InterfaceCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Token\Object\InterfaceMethodCodeToken;
use LesCoder\Token\Object\InterfacePropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\InterfaceRendererSpecification;

#[CoversClass(InterfaceRendererSpecification::class)]
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
        $property = new InterfacePropertyCodeToken($this->createMock(CodeToken::class));
        $method = new InterfaceMethodCodeToken('method');
        $generic = new GenericParameterCodeToken($this->createMock(CodeToken::class));

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(5))
            ->method('render')
            ->willReturnMap(
                [
                    [$extends, 'extended'],
                    [$attribute, 'attribute'],
                    [$property, 'property'],
                    [$method, 'method'],
                    [$generic, 'generic'],
                ],
            );

        $token = new InterfaceCodeToken(
            'Foo',
            [$extends],
            [$attribute],
            [$property],
            [$method],
            [$generic],
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
attribute
export interface Foo<generic> extends extended {
    property;

    method
}

TXT;

        self::assertSame($expected, $rendered);
    }
}
