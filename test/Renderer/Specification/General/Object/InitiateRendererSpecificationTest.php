<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\General\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\InitiateCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\Object\InitiateRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitiateRendererSpecification::class)]
class InitiateRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new InitiateRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new InitiateCodeToken($not, []);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $initiated = $this->createMock(CodeToken::class);

        $parameterOne = $this->createMock(CodeToken::class);
        $parameterTwo = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$initiated, 'initiated'],
                    [$parameterOne, 'parameterOne'],
                    [$parameterTwo, 'parameterTwo'],
                ],
            );

        $token = new InitiateCodeToken($initiated, [$parameterOne, $parameterTwo]);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
new initiated(parameterOne, parameterTwo)
TXT;

        self::assertSame($expected, $rendered);
    }
}
