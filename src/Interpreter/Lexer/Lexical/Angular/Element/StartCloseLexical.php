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
    public const string TYPE = 'angular.element.startClose';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
