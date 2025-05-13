<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\AnonymousFunctionCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class AnonymousFunctionRenderSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true AnonymousFunctionCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof AnonymousFunctionCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(AnonymousFunctionCodeToken::class, $token);
        }

        $codeParameters = [];

        foreach ($token->parameters as $parameter) {
            $codeParameters[] = $renderer->render($parameter);
        }

        $codeParameters = implode(', ', $codeParameters);

        $returns = $token->returns
            ? ": {$renderer->render($token->returns)}"
            : '';

        $body = '';

        foreach ($token->body as $line) {
            $body .= TextUtility::indent($renderer->render($line)) . PHP_EOL;
        }

        return <<<PHP
function ({$codeParameters}){$returns} => {
{$body}}
PHP;
    }
}
