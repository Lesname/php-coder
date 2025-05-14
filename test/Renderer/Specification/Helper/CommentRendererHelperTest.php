<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Helper;

use PHPUnit\Framework\Attributes\CoversTrait;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use PHPUnit\Framework\TestCase;

#[CoversTrait(CommentRendererHelper::class)]
class CommentRendererHelperTest extends TestCase
{
    public function testSingleLine(): void
    {
        $helper = new class {
            use CommentRendererHelper {
                renderComment as public;
            }
        };

        self::assertSame('/** foo */', $helper->renderComment('foo'));
    }

    public function testMultiLine(): void
    {
        $comment = <<<'TXT'
Foo
Bar
TXT;

        $expected = <<<'TXT'
/**
 * Foo
 * Bar
 */
TXT;

        $helper = new class {
            use CommentRendererHelper {
                renderComment as public;
            }
        };

        self::assertSame($expected, $helper->renderComment($comment));
    }
}
