<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Token\Expression\VariableDeclarationCodeToken;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class VariableDeclarationRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true VariableDeclarationCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof VariableDeclarationCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(VariableDeclarationCodeToken::class, $token);
        }

        $modifier = $token->readonly
            ? 'const'
            : 'let';

        if ($token->hint) {
            $hint = ": {$renderer->render($token->hint)}";
        } else {
            $hint = '';
        }

        return "{$modifier} {$token->name}{$hint}";
    }
}
