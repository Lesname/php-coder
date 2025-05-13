<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Object\EnumCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\EnumRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Object\EnumRendererSpecification
 */
class EnumRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new EnumRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new EnumCodeToken('foo', []);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRenderNonBackedEnum(): void
    {
        $implements = $this->createMock(CodeToken::class);
        $use = $this->createMock(CodeToken::class);
        $comment = new CommentCodeToken('foo');

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$implements, 'implement'],
                    [$use, 'used'],
                ],
            );

        $token = new EnumCodeToken(
            'Foo',
            ['One'],
            null,
            [$implements],
            [$use],
            $comment
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
/** foo */
enum Foo implements implement
{
    use used;

    case One;
}
TXT;

        self::assertSame($expected, $rendered);
    }
}
