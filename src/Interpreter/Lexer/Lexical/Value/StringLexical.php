<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Value;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class StringLexical extends AbstractLexical implements ValueLexical
{
    public const TYPE = 'string';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
