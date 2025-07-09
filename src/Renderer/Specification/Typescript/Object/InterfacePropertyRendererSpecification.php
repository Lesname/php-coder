<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Hint\IndexSignatureCodeToken;
use LesCoder\Token\Object\InterfacePropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class InterfacePropertyRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true InterfacePropertyCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof InterfacePropertyCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(InterfacePropertyCodeToken::class, $token);
        }

        $attributes = '';

        foreach ($token->attributes as $attribute) {
            $attributes .= $renderer->render($attribute) . PHP_EOL;
        }

        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : null;

        $hintOperator = $token->required || $token->name instanceof IndexSignatureCodeToken
            ? ':'
            : '?:';

        $hint = $token->hint
            ? ($hintOperator) . ' ' . $renderer->render($token->hint)
            : '';

        if ($token->name instanceof StringCodeToken && preg_match('/^[a-z_\$][a-z\d_\$]*$/i', $token->name->value) === 1) {
            $name = $token->name->value;
        } else {
            $name = $renderer->render($token->name);
        }

        return "{$attributes}{$comment}{$name}{$hint}";
    }
}
