<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

interface Specification
{
    public function isSatisfiedBy(StringStream $code): bool;

    public function parse(StringStream $code): Lexical;
}
