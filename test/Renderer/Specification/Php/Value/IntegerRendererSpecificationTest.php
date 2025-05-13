<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\IntegerRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Value\IntegerRendererSpecification
 */
class IntegerRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new IntegerRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new IntegerCodeToken(1);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $token = new IntegerCodeToken(1234);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
1_234
TXT;

        self::assertSame($expected, $rendered);
    }
}
