<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\AttributeRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Php\AttributeRendererSpecification
 */
class AttributeRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new AttributeRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new AttributeCodeToken(new ReferenceCodeToken('bar'), []);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $parameter = $this->createMock(CodeToken::class);

        $token = new AttributeCodeToken(new ReferenceCodeToken('bar'), [$parameter]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($parameter)
            ->willReturn('parameter');

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
#[bar(parameter)]
TXT;

        self::assertSame($expected, $rendered);
    }
}
