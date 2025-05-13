<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\GenericCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class GenericRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true GenericCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof GenericCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(GenericCodeToken::class, $token);
        }

        $base = $renderer->render($token->base);

        $parameters = array_map(
            fn (CodeToken $parameterToken): string => $renderer->render($parameterToken),
            $token->parameters,
        );

        return $base . '<' . implode(', ', $parameters) . '>';
    }
}
