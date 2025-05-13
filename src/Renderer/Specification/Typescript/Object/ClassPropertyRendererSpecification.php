<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ClassPropertyRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true ClassPropertyCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ClassPropertyCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ClassPropertyCodeToken::class, $token);
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

        $comment = $token->comment
            ? $this->renderComment($token->comment->comment) . PHP_EOL
            : '';

        $hintGlue = $token->flags & ClassPropertyCodeToken::FLAG_OPTIONAL
            ? '?:'
            : ':';

        $hint = $token->hint
            ? $hintGlue . ' ' . $renderer->render($token->hint)
            : '';

        $assigned = $token->assigned
            ? ' = ' . $renderer->render($token->assigned)
            : '';

        $flags = '';

        if ($token->flags & ClassPropertyCodeToken::FLAG_OVERRIDE) {
            $flags .= 'override ';
        }

        if ($token->flags & ClassPropertyCodeToken::FLAG_STATIC) {
            $flags .= 'static ';
        }

        return "{$attributes}{$comment}{$visibility} {$flags}{$token->name}{$hint}{$assigned}";
    }
}
