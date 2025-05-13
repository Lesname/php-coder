<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Value;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Token\LineCodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\ReturnCodeToken;
use LesCoder\Token\Value\DictionaryCodeToken;
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

        if (count($token->body) === 1 && $token->body[0] instanceof LineCodeToken) {
            $line = $token->body[0]->code;

            if ($line instanceof ReturnCodeToken && !$line->code instanceof DictionaryCodeToken) {
                return "({$codeParameters}){$returns} => {$renderer->render($line->code)}";
            }
        }

        $body = '';

        foreach ($token->body as $line) {
            $body .= TextUtility::indent($renderer->render($line)) . PHP_EOL;
        }

        return <<<TYPESCRIPT
({$codeParameters}){$returns} => {
{$body}}
TYPESCRIPT;
    }
}
