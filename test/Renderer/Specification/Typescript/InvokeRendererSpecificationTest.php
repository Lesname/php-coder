<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\InvokeCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\InvokeRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvokeRendererSpecification::class)]
class InvokeRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new InvokeRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new InvokeCodeToken($not, []);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $invoked = $this->createMock(CodeToken::class);
        $parameter = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$invoked, 'invoked'],
                    [$parameter, 'parameter'],
                ],
            );

        $token = new InvokeCodeToken($invoked, [$parameter]);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
invoked(parameter)
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderMultipleParameters(): void
    {
        $invoked = $this->createMock(CodeToken::class);
        $parameterOne = $this->createMock(CodeToken::class);
        $parameterTwo = $this->createMock(CodeToken::class);
        $parameterThree = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(4))
            ->method('render')
            ->willReturnMap(
                [
                    [$invoked, 'invoked'],
                    [$parameterOne, 'parameter1'],
                    [$parameterTwo, 'parameter2'],
                    [$parameterThree, 'parameter3'],
                ],
            );

        $token = new InvokeCodeToken(
            $invoked,
            [
                $parameterOne,
                $parameterTwo,
                $parameterThree,
            ],
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
invoked(
    parameter1,
    parameter2,
    parameter3,
)
TXT;

        self::assertSame($expected, $rendered);
    }
}
