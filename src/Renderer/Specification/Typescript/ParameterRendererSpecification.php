<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\TokenListRenderHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
 use LesCoder\Renderer\Specification\Typescript\Helper\ReservedKeywordHelper;

/**
 * @psalm-immutable
 */
final class ParameterRendererSpecification implements RendererSpecification
{
    use ReservedKeywordHelper;
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

        $code = $this->renderTokenList($token->attributes, $renderer, PHP_EOL);

        if (count($token->attributes) > 1) {
            $code .= PHP_EOL;
        } elseif (count($token->attributes) === 1) {
            $code .= ' ';
        }

        $code .= $this->isReservedKeyword($token->name)
            ? "_{$token->name}"
            : $token->name;

        if ($token->hint) {
            $code .= ($token->optional ? '?' : '') . ': ' . $renderer->render($token->hint);
        } elseif ($token->optional) {
            $code .= '?: ' . $renderer->render(BuiltInCodeToken::Any);
        }

        $code .= $token->assigned
            ? ' = ' . $renderer->render($token->assigned)
            : '';

        return $code;
    }
}
