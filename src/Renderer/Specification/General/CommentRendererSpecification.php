<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class CommentRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true CommentCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof CommentCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(CommentCodeToken::class, $token);
        }

        // Prevent comments to be closed, inject a zero width space
        $comment = str_replace('*/', "*‌/", $token->comment);

        if (str_contains($comment, PHP_EOL)) {
            $comment = implode(PHP_EOL . ' * ', explode(PHP_EOL, $comment));
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
