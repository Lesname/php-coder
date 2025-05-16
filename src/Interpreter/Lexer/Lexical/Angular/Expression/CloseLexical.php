<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Angular\Expression;

use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

final class CloseLexical extends AbstractLexical
{
    public function getType(): string
    {
        return 'angular.expression.close';
    }
}
