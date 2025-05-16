<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;
use LesCoder\Interpreter\Lexer\Specification\Exception\UnexpectedCharacter;

final class IntegerSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return ctype_digit($code->current());
    }

    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $int = $code->current();

        if (!ctype_digit($int)) {
            throw new UnexpectedCharacter($int, 'digit');
        }

        while ($code->isActive() && ctype_digit($code->current())) {
            $int .= $code->current();
            $code->next();
        }

        return new IntegerLexical($int);
    }
}
