<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\AssignmentCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\AssignmentRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Value\AssignmentRendererSpecification
 */
class AssignmentRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new AssignmentRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new AssignmentCodeToken($not, $not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $to = $this->createMock(CodeToken::class);
        $value = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$to, 'To'],
                    [$value, 'Value'],
                ],
            );

        $token = new AssignmentCodeToken($to, $value);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
To = Value
TXT;

        self::assertSame($expected, $rendered);
    }
}
