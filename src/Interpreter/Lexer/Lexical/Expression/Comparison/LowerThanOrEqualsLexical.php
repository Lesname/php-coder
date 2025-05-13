<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class LowerThanOrEqualsLexical extends AbstractLexical implements ComparisonLexical
{
    public const TYPE = 'lowerThanOrEquals';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
