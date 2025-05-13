<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object;

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

        if ($token->flags & ClassMethodCodeToken::FLAG_STATIC) {
            $modifiers .= ' static';
        }

        $codeParameters = [];

        foreach ($token->parameters as $parameter) {
            $codeParameters[] = $renderer->render($parameter);
        }

        $returns = $token->returns
            ? ": {$renderer->render($token->returns)}"
            : '';

        if (count($codeParameters) > 1 || (count($codeParameters) === 1 && str_contains($codeParameters[0], PHP_EOL))) {
            $codeParameters = PHP_EOL
                . TextUtility::indent(implode(',' . PHP_EOL, $codeParameters) . ',')
                . PHP_EOL;
            $returns .= ' ';
        } else {
            $codeParameters = array_pop($codeParameters) ?? '';
            $returns .= PHP_EOL;
        }

        $body = '';

        $previous = null;

        foreach ($token->body as $line) {
            if ($previous && $line::class !== $previous::class) {
                $body .= PHP_EOL;
            }

            $body .= TextUtility::indent($renderer->render($line)) . PHP_EOL;

            $previous = $line;
        }

        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : '';

        if ($body !== '') {
            $body = PHP_EOL . rtrim($body) . PHP_EOL;
        }

        return <<<PHP
{$attributes}{$comment}{$modifiers} function {$token->name}({$codeParameters}){$returns}{{$body}}
PHP;
    }
}
