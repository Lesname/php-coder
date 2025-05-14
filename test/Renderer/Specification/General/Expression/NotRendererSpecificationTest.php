<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\NotCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\NotRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotRendererSpecification::class)]
class NotRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new NotRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new NotCodeToken($not);

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

        $token = new NotCodeToken($subToken);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
!(subToken)
TXT;

        self::assertSame($expected, $rendered);
    }
}
