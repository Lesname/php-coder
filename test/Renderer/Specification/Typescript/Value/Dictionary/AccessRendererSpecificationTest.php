<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Value\Dictionary;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\Dictionary\AccessCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Value\Dictionary\AccessRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\Value\Dictionary\AccessRendererSpecification
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
        $dictionary = $this->createMock(CodeToken::class);
        $key = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$dictionary, 'dict'],
                    [$key, 'key'],
                ],
            );

        $token = new AccessCodeToken($dictionary, $key);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
dict[key]
TXT;

        self::assertSame($expected, $rendered);
    }
}
