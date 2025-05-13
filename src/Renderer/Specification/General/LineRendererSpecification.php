<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\LineCodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class LineRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true LineCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof LineCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(LineCodeToken::class, $token);
        }

        return "{$renderer->render($token->code)};";
    }
}
