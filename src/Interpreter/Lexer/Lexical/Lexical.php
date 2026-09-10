<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical;

use Stringable;

/**
 * @psalm-immutable
 *
 * @deprecated
 */
interface Lexical extends Stringable
{
    public function getType(): string;

    public function isIgnorable(): bool;
}
