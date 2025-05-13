<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\FloatCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\FloatRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Value\FloatRendererSpecification
 */
class FloatRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new FloatRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new FloatCodeToken(1.1);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $token = new FloatCodeToken(12345.6789);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
12_345.6789
TXT;

        self::assertSame($expected, $rendered);
    }
}
