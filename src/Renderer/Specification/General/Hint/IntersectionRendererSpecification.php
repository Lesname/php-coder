<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\IntersectionCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class IntersectionRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true IntersectionCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof IntersectionCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(IntersectionCodeToken::class, $token);
        }

        return implode(
            ' & ',
            array_unique(
                array_map(
                    fn (CodeToken $item): string => $renderer->render($item),
                    $token->items,
                ),
            ),
        );
    }
}
