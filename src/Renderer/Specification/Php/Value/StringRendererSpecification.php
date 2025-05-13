<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class StringRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true StringCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof StringCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(StringCodeToken::class, $token);
        }

        return "'" . addslashes($token->value) . "'";
    }
}
