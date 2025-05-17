<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Angular;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\CodeInterpreter;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Interpreter\Parser\Angular\ExpressionCodeParser;
use LesCoder\Interpreter\Lexer\Angular\ExpressionCodeLexer;

final class ExpressionCodeInterpreter implements CodeInterpreter
{
    #[Override]
    public function interpret(StringStream $stream, ?string $file = null): CodeTokenStream
    {
        return (new ExpressionCodeParser())
            ->parse((new ExpressionCodeLexer())->tokenize($stream), null);
    }
}
