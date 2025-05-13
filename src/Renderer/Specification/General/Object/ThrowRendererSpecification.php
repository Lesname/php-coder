<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\ThrowCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ThrowRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true ThrowCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ThrowCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ThrowCodeToken::class, $token);
        }

        return 'throw ' . $renderer->render($token->code);
    }
}
