<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical;

use Override;

/**
 * @psalm-immutable
 */
interface Lexical
{
    public function getType(): string;

    #[Override]
    public function __toString(): string;

    public function isIgnorable(): bool;
}
