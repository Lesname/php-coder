<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\DictionaryCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Hint\DictionaryRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(DictionaryRendererSpecification::class)]
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

    public function testRenderEmpty(): void
    {
        $token = new DictionaryCodeToken([]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::never())
            ->method('render');

        $rendered = $this->specification->render($token, $renderer);

        self::assertSame('{}', $rendered);
    }

    public function testRenderRequired(): void
    {
        $key = $this->createMock(CodeToken::class);
        $value = $this->createMock(CodeToken::class);

        $token = new DictionaryCodeToken(
            [
                [
                    'key' => $key,
                    'value' => $value,
                    'required' => true,
                ],
            ],
        );

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$key, 'key'],
                    [$value, 'value'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
{ key: value }
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderNotRequired(): void
    {
        $key = $this->createMock(CodeToken::class);
        $value = $this->createMock(CodeToken::class);

        $token = new DictionaryCodeToken(
            [
                [
                    'key' => $key,
                    'value' => $value,
                    'required' => false,
                ],
            ],
        );

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$key, 'key'],
                    [$value, 'value'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
{ key?: value }
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderComment(): void
    {
        $key = $this->createMock(CodeToken::class);
        $value = $this->createMock(CodeToken::class);

        $token = new DictionaryCodeToken(
            [
                [
                    'key' => $key,
                    'value' => $value,
                    'required' => true,
                    'comment' => 'baz'
                ],
            ],
        );

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$key, 'key'],
                    [$value, 'value'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
{
    /** baz */
    key: value,
}
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderMultiItems(): void
    {
        $firstKey = $this->createMock(CodeToken::class);
        $firstValue = $this->createMock(CodeToken::class);
        $secondKey = $this->createMock(CodeToken::class);
        $secondValue = $this->createMock(CodeToken::class);

        $token = new DictionaryCodeToken(
            [
                [
                    'key' => $firstKey,
                    'value' => $firstValue,
                    'required' => false,
                ],
                [
                    'key' => $secondKey,
                    'value' => $secondValue,
                    'required' => true,
                    'comment' => 'baz',
                ],
            ],
        );

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(4))
            ->method('render')
            ->willReturnMap(
                [
                    [$firstKey, 'fKey'],
                    [$firstValue, 'fValue'],
                    [$secondKey, 'sKey'],
                    [$secondValue, 'sValue'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
{
    fKey?: fValue,
    /** baz */
    sKey: sValue,
}
TXT;

        self::assertSame($expected, $rendered);
    }
}
