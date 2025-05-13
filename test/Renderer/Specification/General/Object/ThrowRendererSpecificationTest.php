<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\ThrowCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Object\ThrowRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\General\Object\ThrowRendererSpecification
 */
class ThrowRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ThrowRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ThrowCodeToken($not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $thrown = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($thrown)
            ->willReturn('thrown');

        $token = new ThrowCodeToken($thrown);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
throw thrown
TXT;

        self::assertSame($expected, $rendered);
    }
}
