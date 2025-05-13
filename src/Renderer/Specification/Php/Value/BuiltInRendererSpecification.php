<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\BuiltInCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class BuiltInRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true BuiltInCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof BuiltInCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(BuiltInCodeToken::class, $token);
        }

        return match ($token) {
            BuiltInCodeToken::Null => 'null',
            BuiltInCodeToken::True => 'true',
            BuiltInCodeToken::False => 'false',
            BuiltInCodeToken::Parent => '$parent',
        };
    }
}
