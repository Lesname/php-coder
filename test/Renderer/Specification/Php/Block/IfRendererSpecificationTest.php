<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Block;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Block\IfCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Block\IfRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Block\IfRendererSpecification
 */
class IfRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new IfRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new IfCodeToken($not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRenderIf(): void
    {
        $expression = $this->createMock(CodeToken::class);
        $truthy = $this->createMock(CodeToken::class);

        $token = new IfCodeToken($expression, [$truthy]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$expression, 'expression'],
                    [$truthy, 'truthy'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
if (expression) {
    truthy
}
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderIfElse(): void
    {
        $expression = $this->createMock(CodeToken::class);
        $truthy = $this->createMock(CodeToken::class);
        $falsey = $this->createMock(CodeToken::class);

        $token = new IfCodeToken($expression, [$truthy], [$falsey]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$expression, 'expression'],
                    [$truthy, 'truthy'],
                    [$falsey, 'falsey'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
if (expression) {
    truthy
} else {
    falsey
}
TXT;

        self::assertSame($expected, $rendered);
    }
}
