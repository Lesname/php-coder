<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\GenericCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Hint\GenericRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\Hint\GenericRendererSpecification
 */
class GenericRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new GenericRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new GenericCodeToken($not, []);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $base = $this->createMock(CodeToken::class);
        $parameterOne = $this->createMock(CodeToken::class);
        $parameterTwo = $this->createMock(CodeToken::class);

        $token = new GenericCodeToken($base, [$parameterOne, $parameterTwo]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$base, 'base'],
                    [$parameterOne, 'parameterOne'],
                    [$parameterTwo, 'parameterTwo'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
base<parameterOne, parameterTwo>
TXT;

        self::assertSame($expected, $rendered);
    }
}
