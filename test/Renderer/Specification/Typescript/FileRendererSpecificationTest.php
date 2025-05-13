<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\FileCodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\FileRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\FileRendererSpecification
 */
class FileRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new FileRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new FileCodeToken([]);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $line = $this->createMock(CodeToken::class);
        $line
            ->method('getImports')
            ->willReturn(
                [
                    'foo' => 'bar',
                    'fiz' => 'bar',
                    'biz' => 'fiz',
                ],
            );

        $token = new FileCodeToken([$line]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with($line)
            ->willReturn('line');

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
import {foo, fiz} from 'bar';
import {biz} from 'fiz';

line

TXT;

        self::assertSame($expected, $rendered);
    }
}
