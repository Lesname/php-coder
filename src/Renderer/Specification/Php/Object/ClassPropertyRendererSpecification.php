<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object;

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

        $attributes = [];

        foreach ($token->attributes as $attribute) {
            $attributes[] = $renderer->render($attribute);
        }

        if (count($attributes) > 1) {
            $codeAttributes = implode(PHP_EOL, $attributes) . PHP_EOL;
        } elseif (count($attributes) === 1) {
            $codeAttributes = implode('', $attributes) . ' ';
        } else {
            $codeAttributes = '';
        }

        $visibility = match ($token->visibility) {
            Visibility::Public => 'public ',
            Visibility::Protected => 'protected ',
            Visibility::Private => 'private ',
        };

        $hint = $token->hint
            ? $renderer->render($token->hint) . ' '
            : '';

        $flags = '';

        if ($token->flags & ClassPropertyCodeToken::FLAG_STATIC) {
            $flags .= 'static ';
        }

        if ($token->flags & ClassPropertyCodeToken::FLAG_READONLY) {
            $flags .= 'readonly ';
        }

        $assignment = $token->assigned
            ? ' = ' . $renderer->render($token->assigned)
            : '';

        return ($token->comment ? $this->renderComment($token->comment->comment) . PHP_EOL : '')
            . $codeAttributes
            . $visibility
            . $flags
            . $hint
            . '$' . $token->name
            . $assignment;
    }
}
