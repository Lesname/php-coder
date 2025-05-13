<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\ReturnCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ReturnRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true ReturnCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ReturnCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ReturnCodeToken::class, $token);
        }

        return "return {$renderer->render($token->code)}";
    }
}
