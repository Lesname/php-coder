<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification\Typescript;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Lexical\Character\PlusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Assignment\Math\AdditionAssignmentLexical;

final class PlusSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return $code->current() === '+';
    }

    /**
     * @throws ExpectedExactly
     */
    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $code->expectExactly('+');

        if ($code->current() === '=') {
            $code->next();

            return new AdditionAssignmentLexical('+=');
        }

        return new PlusLexical();
    }
}
