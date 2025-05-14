<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Expression\TypeDeclarationCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\TypeDeclarationRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeDeclarationRendererSpecification::class)]
class TypeDeclarationRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new TypeDeclarationRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new TypeDeclarationCodeToken('foo');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $token = new TypeDeclarationCodeToken('foo');

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
type foo
TXT;

        self::assertSame($expected, $rendered);
    }
}
