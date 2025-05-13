<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Hint;

use A\B;
use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Php\Hint\Exception\NotSupported;

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
            BuiltInCodeToken::Any => 'mixed',
            BuiltInCodeToken::Boolean => 'bool',
            BuiltInCodeToken::Collection,
            BuiltInCodeToken::Dictionary => 'array',
            BuiltInCodeToken::False => 'false',
            BuiltInCodeToken::Integer => 'int',
            BuiltInCodeToken::Float => 'float',
            BuiltInCodeToken::Null => 'null',
            BuiltInCodeToken::String => 'string',
            BuiltInCodeToken::True => 'true',
            BuiltInCodeToken::Void => 'void',
            BuiltInCodeToken::Never => 'never',
            BuiltInCodeToken::Undefined => throw new NotSupported(),
        };
    }
}
