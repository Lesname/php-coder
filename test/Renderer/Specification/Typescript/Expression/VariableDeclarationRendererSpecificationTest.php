<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Token\Expression\VariableDeclarationCodeToken;
use LesCoder\Renderer\Specification\Typescript\Expression\VariableDeclarationRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(VariableDeclarationRendererSpecification::class)]
class VariableDeclarationRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new VariableDeclarationRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new VariableDeclarationCodeToken(false, 'bar');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRenderReadonly(): void
    {
        $hint = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($hint)
            ->willReturn('hint');

        $token = new VariableDeclarationCodeToken(true, 'biz', $hint);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
const biz: hint
TXT;

        self::assertSame($expected, $rendered);
    }
}
