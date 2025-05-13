<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object;

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

        $parameters = [];

        foreach ($token->parameters as $parameter) {
            $parameters[] = "{$renderer->render($parameter)}";
        }

        if (count($parameters) > 1) {
            $codeParameters = PHP_EOL
                . TextUtility::indent(implode(',' . PHP_EOL, $parameters) . ',')
                . PHP_EOL;
        } else {
            $codeParameters = implode('', $parameters);
        }

        $returns = $token->returns
            ? ": {$renderer->render($token->returns)}"
            : '';

        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : '';

        return "{$comment}public function {$token->name}({$codeParameters}){$returns};";
    }
}
