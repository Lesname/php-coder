<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Object;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ClassMethodRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true ClassMethodCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ClassMethodCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ClassMethodCodeToken::class, $token);
        }

        $modifiers = match ($token->visibility) {
            Visibility::Public => 'public',
            Visibility::Protected => 'protected',
            Visibility::Private => 'private',
        };

        $flags = [
            'static' => ClassMethodCodeToken::FLAG_STATIC,
            'override' => ClassMethodCodeToken::FLAG_OVERRIDE,
        ];

        foreach ($flags as $modifier => $bit) {
            if ($token->flags & $bit) {
                $modifiers .= ' ' . $modifier;
            }
        }

        if (count($token->attributes) > 0) {
            $attributes = implode(
                PHP_EOL,
                array_map(
                    static fn(CodeToken $attribute): string => $renderer->render($attribute),
                    $token->attributes,
                ),
            ) . PHP_EOL;
        } else {
            $attributes = '';
        }

        $codeParameters = [];

        foreach ($token->parameters as $parameter) {
            $codeParameters[] = $renderer->render($parameter);
        }

        if (count($codeParameters) > 1) {
            $codeParameters = PHP_EOL
                . TextUtility::indent(implode(',' . PHP_EOL, $codeParameters) . ',')
                . PHP_EOL;
        } else {
            $codeParameters = array_pop($codeParameters) ?? '';
        }

        $returns = $token->returns
            ? ": {$renderer->render($token->returns)}"
            : '';

        $body = '';

        foreach ($token->body as $line) {
            $body .= TextUtility::indent($renderer->render($line)) . PHP_EOL;
        }

        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : null;

        return <<<TYPESCRIPT
{$attributes}{$comment}{$modifiers} {$token->name}({$codeParameters}){$returns} {
{$body}}
TYPESCRIPT;
    }
}
