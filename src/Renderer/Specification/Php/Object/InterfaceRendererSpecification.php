<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object;

use Override;
use RuntimeException;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\InterfaceCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\TokenListRenderHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class InterfaceRendererSpecification implements RendererSpecification
{
    use TokenListRenderHelper;

    /**
     * @psalm-assert-if-true InterfaceCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof InterfaceCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(InterfaceCodeToken::class, $token);
        }

        $header = '';

        foreach ($token->attributes as $attribute) {
            $header .= $renderer->render($attribute) . PHP_EOL;
        }

        $extends = count($token->extends) > 0
            ? ' extends ' . $this->renderTokenList($token->extends, $renderer, ', ')
            : '';

        $body = '';

        if (count($token->properties) > 0) {
            throw new RuntimeException('PHP has no support for interface properties');
        }

        foreach ($token->methods as $method) {
            if ($body !== '') {
                $body .= PHP_EOL;
            }

            $body .= TextUtility::indent($renderer->render($method)) . PHP_EOL;
        }

        return <<<PHP
{$header}interface {$token->name}{$extends}
{
{$body}}

PHP;
    }
}
