<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\NamespaceCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\NamespaceRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Object\NamespaceRendererSpecification
 */
class NamespaceRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new NamespaceRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new NamespaceCodeToken('foo', []);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $line = $this->createMock(CodeToken::class);
        $line
            ->method('getImports')
            ->willReturn(
                [
                    'foo' => '\\bar\\foo',
                    'fiz' => '\\bar\\biz',
                ],
            );

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(1))
            ->method('render')
            ->willReturnMap(
                [
                    [$line, 'line'],
                ],
            );

        $token = new NamespaceCodeToken(
            'Foo',
            [$line],
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
namespace Foo;

use bar\foo;
use bar\biz as fiz;

line

TXT;

        self::assertSame($expected, $rendered);
    }
}
