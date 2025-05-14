<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\CommentRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentRendererSpecification::class)]
class CommentRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new CommentRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new CommentCodeToken('');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRenderSingleLineComment(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $token = new CommentCodeToken('Foo');

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
/** Foo */
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderMultiLineComment(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $token = new CommentCodeToken('Foo' . PHP_EOL . 'bar');

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
/**
 * Foo
 * bar
 */
TXT;

        self::assertSame($expected, $rendered);
    }
}
