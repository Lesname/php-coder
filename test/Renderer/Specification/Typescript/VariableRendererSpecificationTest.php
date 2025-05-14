<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\VariableCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\VariableRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(VariableRendererSpecification::class)]
class VariableRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new VariableRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new VariableCodeToken('foo');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $token = new VariableCodeToken('foo');

        $renderer = $this->createMock(CodeRenderer::class);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
foo
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderReservedKeyword(): void
    {
        $token = new VariableCodeToken('for');

        $renderer = $this->createMock(CodeRenderer::class);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
_for
TXT;

        self::assertSame($expected, $rendered);
    }
}
