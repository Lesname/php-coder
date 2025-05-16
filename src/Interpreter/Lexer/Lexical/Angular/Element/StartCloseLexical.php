<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Angular\Element;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class StartCloseLexical extends AbstractLexical
{
    #[Override]
    public function getType(): string
    {
        return 'angular.element.startClose';
    }
}
