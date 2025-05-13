<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Hint\IndexSignatureCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class IndexSignatureRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true IndexSignatureCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof IndexSignatureCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(IndexSignatureCodeToken::class, $token);
        }

        return "[key: {$renderer->render($token->code)}]";
    }
}
