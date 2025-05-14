<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification\Typescript;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Lexical\Expression\CoalescingLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Lexer\Lexical\Assignment\CoalescingAssignmentLexical;

final class QuestionMarkSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return $code->current() === '?';
    }

    /**
     * @throws ExpectedExactly
     */
    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $code->expectExactly('?');

        if ($code->matchesExactly('?')) {
            $code->next();

            if ($code->matchesExactly('=')) {
                $code->next();

                return new CoalescingAssignmentLexical('??=');
            }

            return new CoalescingLexical('??');
        }

        return new QuestionMarkLexical();
    }
}
