<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer;

use LesCoder\Stream\String\StringStream;
use LesCoder\Stream\Lexical\LexicalStream;

interface CodeLexer
{
    public function tokenize(StringStream $stream): LexicalStream;
}
