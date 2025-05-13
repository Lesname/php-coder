<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Expression;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class OrLexical extends AbstractLexical implements ExpressionLexical
{
    public const TYPE = 'or';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
