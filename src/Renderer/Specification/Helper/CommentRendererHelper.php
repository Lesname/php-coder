<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Helper;

/**
 * @psalm-immutable
 */
trait CommentRendererHelper
{
    protected function renderComment(string $comment): string
    {
        // Prevent comments to be closed, inject a zero width space
        $comment = str_replace('*/', "*‌/", $comment);

        if (str_contains($comment, PHP_EOL)) {
            $comment = implode(PHP_EOL . ' * ', explode(PHP_EOL, $comment));

            // Remove spaces from end of lines
            $comment = preg_replace("/ +(\r?\n|$)/", '$1', $comment);

            return <<<TXT
/**
 * {$comment}
 */
TXT;
        }

        return "/** {$comment} */";
    }
}
