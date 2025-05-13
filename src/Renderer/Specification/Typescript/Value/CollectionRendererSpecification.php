<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\CollectionCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\TokenListRenderHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class CollectionRendererSpecification implements RendererSpecification
{
    use TokenListRenderHelper;

    /**
     * @psalm-assert-if-true CollectionCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof CollectionCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(StringCodeToken::class, $token);
        }

        $list = $this->renderTokenList($token->items, $renderer, ',', true);

        return "[{$list}]";
    }
}
