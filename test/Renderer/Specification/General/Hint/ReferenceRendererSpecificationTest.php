<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\ReferenceCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Hint\ReferenceRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReferenceRendererSpecification::class)]
class ReferenceRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ReferenceRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ReferenceCodeToken('foo');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $token = new ReferenceCodeToken('foo', 'bar');

        $renderer = $this->createMock(CodeRenderer::class);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
foo
TXT;

        self::assertSame($expected, $rendered);
    }
}
