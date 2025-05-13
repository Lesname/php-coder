<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\TokenListRenderHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ParameterRendererSpecification implements RendererSpecification
{
    use TokenListRenderHelper;

    /**
     * @psalm-assert-if-true ParameterCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ParameterCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ParameterCodeToken::class, $token);
        }

        $code = '';

        foreach ($token->attributes as $attribute) {
            $code .= $renderer->render($attribute) . PHP_EOL;
        }

        $code .= $token->hint
            ? $renderer->render($token->hint)
            : '';

        $code .= ' $' . $token->name;

        $code .= $token->assigned
            ? ' = ' . $renderer->render($token->assigned)
            : '';

        return $code;
    }
}
