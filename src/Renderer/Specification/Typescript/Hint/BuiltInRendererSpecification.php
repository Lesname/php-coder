<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\BuiltInCodeToken;
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
            BuiltInCodeToken::Any => 'any',
            BuiltInCodeToken::Boolean => 'boolean',
            BuiltInCodeToken::Collection => 'Array',
            BuiltInCodeToken::Dictionary => '{}',
            BuiltInCodeToken::False => 'false',
            BuiltInCodeToken::Integer,
            BuiltInCodeToken::Float => 'number',
            BuiltInCodeToken::Null => 'null',
            BuiltInCodeToken::String => 'string',
            BuiltInCodeToken::True => 'true',
            BuiltInCodeToken::Void => 'void',
            BuiltInCodeToken::Never => 'never',
        };
    }
}
