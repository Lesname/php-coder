<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Expression;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\GroupCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class GroupRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true GroupCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof GroupCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(GroupCodeToken::class, $token);
        }

        $group = $renderer->render($token->code);

        if (str_contains($group, PHP_EOL)) {
            return '(' . PHP_EOL
                . TextUtility::indent($group) . PHP_EOL
                . ')';
        }

        return "({$group})";
    }
}
