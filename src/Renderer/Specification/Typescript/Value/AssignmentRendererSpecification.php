<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\AssignmentCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class AssignmentRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true AssignmentCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof AssignmentCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(AssignmentCodeToken::class, $token);
        }

        return "{$renderer->render($token->to)} = {$renderer->render($token->value)}";
    }
}
