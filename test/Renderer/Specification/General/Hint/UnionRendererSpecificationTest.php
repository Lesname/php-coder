<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\UnionCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Hint\UnionRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnionRendererSpecification::class)]
class UnionRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new UnionRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new UnionCodeToken([]);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $first = $this->createMock(CodeToken::class);
        $second = $this->createMock(CodeToken::class);

        $token = new UnionCodeToken([$first, $second]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$first, 'first'],
                    [$second, 'second'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
first | second
TXT;

        self::assertSame($expected, $rendered);
    }
}
