<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Helper;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Renderer\Specification\Helper\TokenBodyRendererHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Helper\TokenBodyRendererHelper
 */
class TokenBodyRendererHelperTest extends TestCase
{
    public function testRenderTokenBody(): void
    {
        $first = $this->createMock(CodeToken::class);
        $second = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$first, 'first'],
                    [$second, 'second'],
                ],
            );

        $expect = <<<'TXT'
    first
    second

TXT;

        $helper = new class {
            use TokenBodyRendererHelper {
                renderTokenBody as public;
            }
        };

        self::assertSame($expect, $helper->renderTokenBody([$first, $second], $renderer));
    }

    public function testRenderTokenBodyEmpty(): void
    {
        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::never())
            ->method('render');

        $helper = new class {
            use TokenBodyRendererHelper {
                renderTokenBody as public;
            }
        };

        self::assertSame('', $helper->renderTokenBody([], $renderer));
    }
}
