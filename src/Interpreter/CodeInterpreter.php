<?php
declare(strict_types=1);

namespace LesCoder\Interpreter;

use LesCoder\Stream\String\StringStream;
use LesCoder\Stream\CodeToken\CodeTokenStream;

interface CodeInterpreter
{
    public function interpret(StringStream $stream, ?string $file = null): CodeTokenStream;
}
