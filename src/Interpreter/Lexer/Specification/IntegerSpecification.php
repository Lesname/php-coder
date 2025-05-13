<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use RuntimeException;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;

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
        $int = '';

        while (ctype_digit($code->current()) && $code->isActive()) {
            $int .= $code->current();
            $code->next();
        }

        if (strlen($int) === 0) {
            throw new RuntimeException();
        }

        return new IntegerLexical($int);
    }
}
