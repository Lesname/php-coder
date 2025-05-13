<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Typescript\Helper\ReservedKeywordHelper;

/**
 * @psalm-immutable
 */
final class VariableRendererSpecification implements RendererSpecification
{
    use ReservedKeywordHelper;

    /**
     * @psalm-assert-if-true VariableCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof VariableCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(VariableCodeToken::class, $token);
        }

        return $this->isReservedKeyword($token->name)
            ? "_{$token->name}"
            : $token->name;
    }
}
