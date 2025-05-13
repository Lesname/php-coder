<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Assignment;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class CoalescingAssignmentLexical extends AbstractLexical implements AssignmentLexical
{
    public const TYPE = 'coalescingAssignment';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
