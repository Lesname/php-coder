<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\MathOperator;
use LesCoder\Token\Expression\CalculationCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\CalculationRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\General\Expression\CalculationRendererSpecification
 */
class CalculationRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new CalculationRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new CalculationCodeToken(
            $not,
            $not,
            MathOperator::Divide,
        );

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $left = $this->createMock(CodeToken::class);
        $right = $this->createMock(CodeToken::class);

        $operator = MathOperator::Divide;

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

        $token = new CalculationCodeToken($left, $right, $operator);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
left / right
TXT;

        self::assertSame($expected, $rendered);
    }
}
