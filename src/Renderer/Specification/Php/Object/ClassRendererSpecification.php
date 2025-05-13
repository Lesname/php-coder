<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\ClassCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ClassRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true ClassCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ClassCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ClassCodeToken::class, $token);
        }

        $header = '';

        foreach ($token->attributes as $attribute) {
            $header .= $renderer->render($attribute) . PHP_EOL;
        }

        if ($token->comment) {
            $header .= $this->renderComment($token->comment->comment) . PHP_EOL;
        }

        $extends = $token->extends
            ? ' extends ' . $renderer->render($token->extends)
            : '';

        $implements = [];

        foreach ($token->implements as $implement) {
            $implements[] = $renderer->render($implement);
        }

        $implements = count($implements) > 0
            ? ' implements ' . implode(', ', $implements)
            : '';

        $body = '';

        foreach ($token->properties as $property) {
            $body .= TextUtility::indent("{$renderer->render($property)};") . PHP_EOL;
        }

        foreach ($token->methods as $method) {
            if ($body !== '') {
                $body .= PHP_EOL;
            }

            $body .= TextUtility::indent($renderer->render($method)) . PHP_EOL;
        }

        $flags = '';

        if ($token->flags & ClassCodeToken::FLAG_ABSTRACT) {
            $flags .= 'abstract ';
        }

        if ($token->flags & ClassCodeToken::FLAG_FINAL) {
            $flags .= 'final ';
        }

        if ($token->flags & ClassCodeToken::FLAG_READONLY) {
            $flags .= 'readonly ';
        }

        return <<<PHP
{$header}{$flags}class {$token->name}{$extends}{$implements}
{
{$body}}

PHP;
    }
}
