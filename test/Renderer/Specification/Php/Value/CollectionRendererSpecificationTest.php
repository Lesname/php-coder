<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\CollectionCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\CollectionRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectionRendererSpecification::class)]
class CollectionRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new CollectionRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        self::assertTrue($this->specification->canRender(new CollectionCodeToken([])));
        self::assertTrue($this->specification->canRender(new CollectionCodeToken([])));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRenderEmpty(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $token = new CollectionCodeToken([]);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
[]
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderSingleItem(): void
    {
        $item = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(1))
            ->method('render')
            ->willReturnMap(
                [
                    [$item, 'item'],
                ],
            );

        $token = new CollectionCodeToken([$item]);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
[item]
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderMultipleItems(): void
    {
        $one = $this->createMock(CodeToken::class);
        $two = $this->createMock(CodeToken::class);
        $three = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$one, '1'],
                    [$two, '2'],
                    [$three, '3'],
                ],
            );

        $token = new CollectionCodeToken([$one, $two, $three]);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
[
    1,
    2,
    3,
]
TXT;

        self::assertSame($expected, $rendered);
    }
}
