<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\FloatCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class FloatRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true FloatCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof FloatCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(FloatCodeToken::class, $token);
        }

        preg_match('/(?<decimals>\d+)$/', (string)$token->value, $matches);

        return number_format($token->value, strlen($matches['decimals'] ?? ''), thousands_separator: '_');
    }
}
