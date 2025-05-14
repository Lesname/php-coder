<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Hint\IndexSignatureCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Hint\IndexSignatureRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexSignatureRendererSpecification::class)]
class IndexSignatureRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new IndexSignatureRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new IndexSignatureCodeToken($not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $signature = $this->createMock(CodeToken::class);

        $token = new IndexSignatureCodeToken($signature);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($signature)
            ->willReturn('signature');

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
[key: signature]
TXT;

        self::assertSame($expected, $rendered);
    }
}
