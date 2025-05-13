<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;

final class LabelSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
         return preg_match('/[a-zA-Z\x7f-\xff_$]/', $code->current()) === 1;
    }

    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $label = $code->current();
        $code->next();

        while ($code->isActive() && preg_match('/[a-zA-Z0-9\x7f-\xff_$]/', $code->current())) {
            $label .= $code->current();
            $code->next();
        }

        return new LabelLexical($label);
    }
}
