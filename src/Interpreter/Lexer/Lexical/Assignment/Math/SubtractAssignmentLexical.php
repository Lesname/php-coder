<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Assignment\Math;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;
use LesCoder\Interpreter\Lexer\Lexical\Assignment\AssignmentLexical;

/**
 * @psalm-immutable
 */
final class SubtractAssignmentLexical extends AbstractLexical implements AssignmentLexical
{
    public const TYPE = 'subtractAssignment';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
