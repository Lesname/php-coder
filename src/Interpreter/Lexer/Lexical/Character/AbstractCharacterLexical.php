<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character;

use LesCoder\Interpreter\Lexer\Lexical\AbstractLexical;

/**
 * @psalm-immutable
 */
abstract class AbstractCharacterLexical extends AbstractLexical
{
    final public function __construct()
    {
        parent::__construct($this->character());
    }

    abstract protected function character(): string;
}
