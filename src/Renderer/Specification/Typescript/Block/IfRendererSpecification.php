<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Block;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Block\IfCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Helper\TokenBodyRendererHelper;

/**
 * @psalm-immutable
 */
final class IfRendererSpecification implements RendererSpecification
{
    use TokenBodyRendererHelper;

    /**
     * @psalm-assert-if-true IfCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof IfCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(IfCodeToken::class, $token);
        }

        $expression = $renderer->render($token->expression);
        $truthy = $this->renderTokenBody($token->truthy, $renderer);

        if (count($token->falsey) === 0) {
            return <<<TXT
if ({$expression}) {
{$truthy}}
TXT;
        }

        $falsey = $this->renderTokenBody($token->falsey, $renderer);

        return <<<TXT
if ({$expression}) {
{$truthy}} else {
{$falsey}}
TXT;
    }
}
