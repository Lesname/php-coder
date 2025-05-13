<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\OrCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\OrRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\General\Expression\OrRendererSpecification
 */
class OrRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new OrRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new OrCodeToken([]);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $first = $this->createMock(CodeToken::class);
        $second = $this->createMock(CodeToken::class);

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

        $token = new OrCodeToken([$first, $second]);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
first || second
TXT;

        self::assertSame($expected, $rendered);
    }
}
