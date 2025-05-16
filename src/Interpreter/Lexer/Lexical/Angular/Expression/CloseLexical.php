<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Angular\Expression;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class CloseLexical extends AbstractLexical
{
    #[Override]
    public function getType(): string
    {
        return 'angular.expression.close';
    }
}
