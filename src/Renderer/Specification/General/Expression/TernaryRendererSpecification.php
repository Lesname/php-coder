<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\TernaryCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class TernaryRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true TernaryCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof TernaryCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(TernaryCodeToken::class, $token);
        }

        $expression = $renderer->render($token->expression);
        $truthy = $renderer->render($token->truthy);
        $falsey = $renderer->render($token->falsey);

        $multi = str_contains($expression . $truthy . $falsey, PHP_EOL);

        if ($multi) {
            return $expression . PHP_EOL
                . TextUtility::indent("? {$truthy}") . PHP_EOL
                . TextUtility::indent(": {$falsey}");
        }

        return "{$expression} ? {$truthy} : {$falsey}";
    }
}
