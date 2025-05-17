<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Angular;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\CodeInterpreter;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Interpreter\Lexer\Angular\TemplateCodeLexer;
use LesCoder\Interpreter\Parser\Angular\TemplateCodeParser;

final class TemplateCodeInterpreter implements CodeInterpreter
{
    #[Override]
    public function interpret(StringStream $stream, ?string $file = null): CodeTokenStream
    {
        return (new TemplateCodeParser())
            ->parse((new TemplateCodeLexer())->tokenize($stream), null);
    }
}
