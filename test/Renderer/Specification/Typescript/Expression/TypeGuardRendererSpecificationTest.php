<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Expression\TypeGuardCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\TypeGuardRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeGuardRendererSpecification::class)]
class TypeGuardRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new TypeGuardRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new TypeGuardCodeToken('var', $not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $as = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($as)
            ->willReturn('as');

        $token = new TypeGuardCodeToken('var', $as);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
var is as
TXT;

        self::assertSame($expected, $rendered);
    }
}
