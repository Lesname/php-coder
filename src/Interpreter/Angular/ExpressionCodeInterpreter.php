<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Angular;

use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\CodeInterpreter;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Interpreter\Lexer\AngularExpressionCodeLexer;
use LesCoder\Interpreter\Parser\Angular\ExpressionCodeParser;

final class ExpressionCodeInterpreter implements CodeInterpreter
{
    public function interpret(StringStream $stream, ?string $file = null): CodeTokenStream
    {
        return (new ExpressionCodeParser())
            ->parse((new AngularExpressionCodeLexer())->tokenize($stream), null);
    }
}
