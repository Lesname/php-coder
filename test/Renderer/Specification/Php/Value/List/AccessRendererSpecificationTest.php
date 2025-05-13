<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value\List;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\List\AccessRendererSpecification;
use LesCoder\Token\Value\List\AccessCodeToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Value\List\AccessRendererSpecification
 */
class AccessRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new AccessRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new AccessCodeToken($not, $not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $list = $this->createMock(CodeToken::class);
        $index = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$list, 'list'],
                    [$index, 'index'],
                ],
            );

        $token = new AccessCodeToken($list, $index);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
list[index]
TXT;

        self::assertSame($expected, $rendered);
    }
}
