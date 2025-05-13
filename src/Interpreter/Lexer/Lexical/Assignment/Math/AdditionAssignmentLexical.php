<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Assignment\Math;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;
use LesCoder\Interpreter\Lexer\Lexical\Assignment\AssignmentLexical;

/**
 * @psalm-immutable
 */
final class AdditionAssignmentLexical extends AbstractLexical implements AssignmentLexical
{
    public const TYPE = 'additionAssignment';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
