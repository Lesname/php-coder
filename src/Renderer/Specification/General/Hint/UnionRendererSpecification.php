<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\UnionCodeToken;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class UnionRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true UnionCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof UnionCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(UnionCodeToken::class, $token);
        }

        $unions = [];

        foreach ($token->items as $item) {
            if ($item === BuiltInCodeToken::Any) {
                return $renderer->render($item);
            }

            $unions[] = $renderer->render($item);
        }

        return implode(' | ', array_unique($unions));
    }
}
