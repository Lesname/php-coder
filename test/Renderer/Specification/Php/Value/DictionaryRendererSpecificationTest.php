<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\Dictionary\Item;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\DictionaryRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Value\DictionaryRendererSpecification
 */
class DictionaryRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new DictionaryRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new DictionaryCodeToken([]);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $keyOne = $this->createMock(CodeToken::class);
        $valueOne = $this->createMock(CodeToken::class);
        $keyTwo = $this->createMock(CodeToken::class);
        $valueTwo = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(4))
            ->method('render')
            ->willReturnMap(
                [
                    [$keyOne, 'Key1'],
                    [$valueOne, 'Value1'],
                    [$keyTwo, 'Key2'],
                    [$valueTwo, 'Value2'],
                ],
            );

        $token = new DictionaryCodeToken(
            [
                new Item($keyOne, $valueOne),
                new Item($keyTwo, $valueTwo),
            ],
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
[
    Key1 => Value1,
    Key2 => Value2,
]
TXT;

        self::assertSame($expected, $rendered);
    }
}
