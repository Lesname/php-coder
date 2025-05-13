<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Value\List;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\List\AccessCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class AccessRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true AccessCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof AccessCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(AccessCodeToken::class, $token);
        }

        return "{$renderer->render($token->list)}[{$renderer->render($token->index)}]";
    }
}
