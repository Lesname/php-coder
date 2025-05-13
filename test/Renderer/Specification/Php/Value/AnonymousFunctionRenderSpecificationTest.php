<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Value\AnonymousFunctionCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\AnonymousFunctionRenderSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Value\AnonymousFunctionRenderSpecification
 */
class AnonymousFunctionRenderSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new AnonymousFunctionRenderSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new AnonymousFunctionCodeToken();

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $parameter = new ParameterCodeToken('name');
        $returns = $this->createMock(CodeToken::class);
        $line = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$parameter, 'Parameter'],
                    [$returns, 'Returns'],
                    [$line, 'Line'],
                ],
            );

        $token = new AnonymousFunctionCodeToken(
            [$parameter],
            $returns,
            [$line],
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
function (Parameter): Returns => {
    Line
}
TXT;

        self::assertSame($expected, $rendered);
    }
}
