<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Angular\FlowControl;

use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
final class StartLexical extends AbstractLexical
{
    public const string TYPE = 'angular.flowControl.start';

    public function getType(): string
    {
        return self::TYPE;
    }
}
