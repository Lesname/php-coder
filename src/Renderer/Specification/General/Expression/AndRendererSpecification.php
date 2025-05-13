<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\AndCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class AndRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true AndCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof AndCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(AndCodeToken::class, $token);
        }

        return implode(
            ' && ',
            array_map(
                static fn (CodeToken $token): string => $renderer->render($token),
                $token->items,
            ),
        );
    }
}
