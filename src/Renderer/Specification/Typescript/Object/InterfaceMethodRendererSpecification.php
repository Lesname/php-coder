<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Object;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\InterfaceMethodCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class InterfaceMethodRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true InterfaceMethodCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof InterfaceMethodCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(InterfaceMethodCodeToken::class, $token);
        }

        $codeParameters = count($token->parameters) > 1 ? PHP_EOL : '';

        foreach ($token->parameters as $parameter) {
            if (count($token->parameters) > 1) {
                $codeParameters .= TextUtility::indent("{$renderer->render($parameter)},") . PHP_EOL;
            } else {
                $codeParameters .= $renderer->render($parameter);
            }
        }

        $returns = $token->returns
            ? ": {$renderer->render($token->returns)}"
            : '';

        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : '';

        return "{$comment}{$token->name}({$codeParameters}){$returns};";
    }
}
