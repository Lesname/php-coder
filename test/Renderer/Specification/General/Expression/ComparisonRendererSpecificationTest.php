<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Expression\ComparisonOperator;
use LesCoder\Token\Expression\ComparisonCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\ComparisonRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComparisonRendererSpecification::class)]
class ComparisonRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ComparisonRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ComparisonCodeToken($not, $not, ComparisonOperator::Equal);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $left = $this->createMock(CodeToken::class);
        $right = $this->createMock(CodeToken::class);

        $operator = ComparisonOperator::Equal;

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$left, 'left'],
                    [$right, 'right'],
                ],
            );

        $token = new ComparisonCodeToken(
            $left,
            $right,
            $operator,
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
left == right
TXT;

        self::assertSame($expected, $rendered);
    }
}
