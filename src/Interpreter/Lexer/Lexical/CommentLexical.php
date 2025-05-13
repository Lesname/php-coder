<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical;

use Override;

/**
 * @psalm-immutable
 */
final class CommentLexical extends AbstractLexical
{
    public const TYPE = 'comment';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }

    #[Override]
    public function isIgnorable(): bool
    {
        return true;
    }
}
