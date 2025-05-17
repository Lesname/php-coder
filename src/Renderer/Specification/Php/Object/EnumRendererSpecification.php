<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\EnumCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Php\Object\Exception\ReplaceFailed;

/**
 * @psalm-immutable
 */
final class EnumRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true EnumCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof EnumCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(EnumCodeToken::class, $token);
        }


        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : '';

        $backs = $token->backs
            ? ': ' . $renderer->render($token->backs)
            : '';

        $implements = '';

        if (count($token->implements)) {
            $implements = [];

            foreach ($token->implements as $implement) {
                $implements[] = $renderer->render($implement);
            }

            $implements = ' implements ' . implode(', ', $implements);
        }

        $body = '';

        foreach ($token->uses as $use) {
            $body .= 'use ' . $renderer->render($use) . ';' . PHP_EOL;
        }

        $body .= count($token->uses) > 0
            ? PHP_EOL
            : '';

        $slugger = function (string $input): string {
            $replaced = preg_replace('/([^a-zA-Z]+|[^a-zA-Z\d])/', '', $input);

            if (!is_string($replaced)) {
                throw new ReplaceFailed($input);
            }

            return $replaced;
        };

        foreach ($token->cases as $index => $value) {
            if ($value instanceof CodeToken) {
                assert(is_string($index));

                $body .= "case {$slugger($index)} = {$renderer->render($value)};";
            } else {
                $body .= "case {$slugger($value)};";
            }

            $body .= PHP_EOL;
        }

        $body = TextUtility::indent(trim($body));

        return <<<PHP
{$comment}enum {$token->name}{$backs}{$implements}
{
{$body}
}
PHP;
    }
}
