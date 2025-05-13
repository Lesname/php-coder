<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Helper;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Renderer\Specification\Helper\TokenListRenderHelper;

/**
 * @covers \LesCoder\Renderer\Specification\Helper\TokenListRenderHelper
 */
class TokenListRenderHelperTest extends TestCase
{
    public function testSimple(): void
    {
        $parameterOne = $this->createMock(CodeToken::class);
        $parameterTwo = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$parameterOne, 'foo'],
                    [$parameterTwo, 'bar'],
                ],
            );

        $helper = new class {
            use TokenListRenderHelper {
                renderTokenList as public;
            }
        };

        $rendered = $helper->renderTokenList(
            [$parameterOne, $parameterTwo],
            $renderer,
            ','
        );

        self::assertSame('foo, bar', $rendered);
    }

    public function testMultiParamsToMultiLine(): void
    {
        $parameterOne = $this->createMock(CodeToken::class);
        $parameterTwo = $this->createMock(CodeToken::class);
        $parameterThree = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$parameterOne, 'foo'],
                    [$parameterTwo, 'bar'],
                    [$parameterThree, 'biz'],
                ],
            );

        $helper = new class {
            use TokenListRenderHelper {
                renderTokenList as public;
            }
        };

        $rendered = $helper->renderTokenList(
            [$parameterOne, $parameterTwo, $parameterThree],
            $renderer,
            ' |'
        );

        $expect = <<<'TXT'

    foo |
    bar |
    biz

TXT;


        self::assertSame($expect, $rendered);
    }

    public function testLongTextToMultiLine(): void
    {
        $parameterOne = $this->createMock(CodeToken::class);
        $parameterTwo = $this->createMock(CodeToken::class);

        $a = str_repeat('a', 50);
        $b = str_repeat('b', 50);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$parameterOne, $a],
                    [$parameterTwo, $b],
                ],
            );

        $helper = new class {
            use TokenListRenderHelper {
                renderTokenList as public;
            }
        };

        $rendered = $helper->renderTokenList(
            [$parameterOne, $parameterTwo],
            $renderer,
            '&',
        );

        $expect = <<<TXT

    {$a}&
    {$b}

TXT;


        self::assertSame($expect, $rendered);
    }
}
