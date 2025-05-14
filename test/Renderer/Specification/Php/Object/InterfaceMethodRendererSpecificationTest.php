<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Object\InterfaceMethodCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\InterfaceMethodRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Object\InterfaceMethodRendererSpecification
 */
class InterfaceMethodRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new InterfaceMethodRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new InterfaceMethodCodeToken('baz');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $parameter = new ParameterCodeToken('param');
        $returns = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$parameter, 'Parameter'],
                    [$returns, 'Returns'],
                ],
            );

        $comment = new CommentCodeToken('Comment');

        $token = new InterfaceMethodCodeToken(
            'name',
            [$parameter],
            $returns,
            true,
            $comment,
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
/** Comment */
public function name(Parameter): Returns;
TXT;

        self::assertSame($expected, $rendered);
    }
}
