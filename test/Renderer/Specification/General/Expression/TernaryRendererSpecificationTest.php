<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\TernaryCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\TernaryRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\General\Expression\TernaryRendererSpecification
 */
class TernaryRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new TernaryRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new TernaryCodeToken($not, $not, $not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $expression = $this->createMock(CodeToken::class);
        $truthy = $this->createMock(CodeToken::class);
        $falsey = $this->createMock(CodeToken::class);

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

        $token = new TernaryCodeToken($expression, $truthy, $falsey);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
expression ? truthy : falsey
TXT;

        self::assertSame($expected, $rendered);
    }
}
