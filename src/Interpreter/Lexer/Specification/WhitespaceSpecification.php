<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;

final class WhitespaceSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return preg_match("/^\s$/", $code->current()) === 1;
    }

    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $whitespace = $code->current();
        $code->next();

        while ($code->isActive() && preg_match("/^\s$/", $code->current()) === 1) {
            $whitespace .= $code->current();
            $code->next();
        }

        return new WhitespaceLexical($whitespace);
    }
}
