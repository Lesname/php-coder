<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class NotSameLexical extends AbstractLexical implements ComparisonLexical
{
    public const TYPE = 'notSame';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
