<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Object;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\Object\ClassGetPropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ClassGetPropertyRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true ClassGetPropertyCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ClassGetPropertyCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ClassGetPropertyCodeToken::class, $token);
        }

        $visibility = match ($token->visibility) {
            Visibility::Public => 'public',
            Visibility::Protected => 'protected',
            Visibility::Private => 'private',
        };

        $attributes = implode(
            PHP_EOL,
            array_map(
                static fn (CodeToken $attribute): string => $renderer->render($attribute),
                $token->attributes,
            ),
        );

        if (count($token->attributes) > 1 || $token->comment) {
            $attributes .= PHP_EOL;
        } elseif (count($token->attributes) === 1) {
            $attributes .= ' ';
        }

        $hint = $token->hint
            ? ': ' . $renderer->render($token->hint)
            : '';

        $body = '';

        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : '';

        foreach ($token->body as $line) {
            $body .= TextUtility::indent($renderer->render($line)) . PHP_EOL;
        }

        $flags = '';

        if ($token->flags & ClassGetPropertyCodeToken::FLAG_OVERRIDE) {
            $flags .= 'override ';
        }

        if ($token->flags & ClassGetPropertyCodeToken::FLAG_STATIC) {
            $flags .= 'static ';
        }

        return <<<TYPESCRIPT
{$attributes}{$comment}{$visibility} {$flags}get {$token->name}(){$hint} {
{$body}}
TYPESCRIPT;
    }
}
