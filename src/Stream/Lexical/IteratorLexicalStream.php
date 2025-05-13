<?php
declare(strict_types=1);

namespace LesCoder\Stream\Lexical;


use Iterator;
use Override;
use RuntimeException;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

final class IteratorLexicalStream extends AbstractLexicalStream
{
    /**
     * @param Iterator<Lexical> $iterator
     */
    public function __construct(private readonly Iterator $iterator)
    {}

    #[Override]
    public function current(int $length = 1): Lexical
    {
        if ($length > 1) {
            throw new RuntimeException();
        }

        return $this->iterator->current();
    }

    #[Override]
    public function next(int $size = 1): void
    {
        if ($size > 1) {
            throw new RuntimeException();
        }

        $this->iterator->next();
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->iterator->valid();
    }
}
