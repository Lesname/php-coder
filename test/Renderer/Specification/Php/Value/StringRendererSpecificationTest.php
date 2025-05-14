<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\StringCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\StringRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringRendererSpecification::class)]
class StringRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new StringRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new StringCodeToken('foo');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $token = new StringCodeToken('foo');

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
'foo'
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderWithEnter(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $text = <<<'TXT'
Foo
Bar
TXT;

        $token = new StringCodeToken($text);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
'Foo
Bar'
TXT;

        self::assertSame($expected, $rendered);
    }
}
