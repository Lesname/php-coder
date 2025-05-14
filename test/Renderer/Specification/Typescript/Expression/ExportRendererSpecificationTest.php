<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Expression\ExportCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\ExportRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExportRendererSpecification::class)]
class ExportRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ExportRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ExportCodeToken($not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $exported = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($exported)
            ->willReturn('exported');

        $token = new ExportCodeToken($exported);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
export exported
TXT;

        self::assertSame($expected, $rendered);
    }
}
