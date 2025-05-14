<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\GroupCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\GroupRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupRendererSpecification::class)]
class GroupRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new GroupRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new GroupCodeToken($not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $subToken = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($subToken)
            ->willReturn('subToken');

        $token = new GroupCodeToken($subToken);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
(subToken)
TXT;

        self::assertSame($expected, $rendered);
    }
}
