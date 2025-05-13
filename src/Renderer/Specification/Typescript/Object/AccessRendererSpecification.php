<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Object;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class AccessRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true AccessCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof AccessCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(AccessCodeToken::class, $token);
        }

        if ($token->property instanceof StringCodeToken && preg_match('/^[a-z_\$][a-z\d_\$]*$/i', $token->property->value) === 1) {
            $property = $token->property->value;
            $format = $token->isNullable()
                ? '?.%s'
                : '.%s';
        } else {
            $property = $renderer->render($token->property);
            $format = $token->isNullable()
                ? '?.[%s]'
                : '[%s]';
        }

        $indent = str_contains($property, PHP_EOL);

        if ($indent) {
            $format = PHP_EOL . TextUtility::indent($format);
        }

        return $renderer->render($token->called) . sprintf($format, $property);
    }
}
