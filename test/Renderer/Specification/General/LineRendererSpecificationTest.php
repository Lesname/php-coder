<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\LineCodeToken;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\LineRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(LineRendererSpecification::class)]
class LineRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new LineRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new LineCodeToken($not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $code = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($code)
            ->willReturn('code');

        $token = new LineCodeToken($code);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
code;
TXT;

        self::assertSame($expected, $rendered);
    }
}
