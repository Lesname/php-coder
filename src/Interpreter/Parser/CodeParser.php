<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser;

use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Stream\CodeToken\CodeTokenStream;

interface CodeParser
{
    public function parse(LexicalStream $stream, ?string $file): CodeTokenStream;
}
