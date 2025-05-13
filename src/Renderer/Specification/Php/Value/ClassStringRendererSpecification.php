<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\ClassStringCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ClassStringRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true ClassStringCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ClassStringCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ClassStringCodeToken::class, $token);
        }

        return "{$token->class}::class";
    }
}
