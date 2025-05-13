<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\DowncastCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\DowncastRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\Expression\DowncastRendererSpecification
 */
class DowncastRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new DowncastRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new DowncastCodeToken($not, $not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $expression = $this->createMock(CodeToken::class);
        $to = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$expression, 'expression'],
                    [$to, 'to'],
                ],
            );

        $token = new DowncastCodeToken($expression, $to);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
expression as to
TXT;

        self::assertSame($expected, $rendered);
    }
}
