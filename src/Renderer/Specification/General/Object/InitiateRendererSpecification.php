<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\InitiateCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Helper\TokenListRenderHelper;

/**
 * @psalm-immutable
 */
final class InitiateRendererSpecification implements RendererSpecification
{
    use TokenListRenderHelper;

    /**
     * @psalm-assert-if-true InitiateCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof InitiateCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(InitiateCodeToken::class, $token);
        }

        return sprintf(
            'new %s(%s)',
            $renderer->render($token->initiated),
            $this->renderTokenList($token->parameters, $renderer, ','),
        );
    }
}
