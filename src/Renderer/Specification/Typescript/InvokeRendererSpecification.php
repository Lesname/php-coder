<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\InvokeCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\TokenListRenderHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class InvokeRendererSpecification implements RendererSpecification
{
    use TokenListRenderHelper;

    /**
     * @psalm-assert-if-true InvokeCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof InvokeCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(InvokeCodeToken::class, $token);
        }

        $invoke = $renderer->render($token->invoke);
        $renderedParameterList = $this->renderTokenList($token->parameters, $renderer, ',', true);

        return "{$invoke}({$renderedParameterList})";
    }
}
