<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical;

use Override;

/**
 * @psalm-immutable
 */
abstract class AbstractLexical implements Lexical
{
    public function __construct(private readonly string $represents)
    {}

    #[Override]
    public function __toString(): string
    {
        return $this->represents;
    }

    #[Override]
    public function isIgnorable(): bool
    {
        return false;
    }
}
