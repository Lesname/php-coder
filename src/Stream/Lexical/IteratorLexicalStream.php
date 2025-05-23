<?php
declare(strict_types=1);

namespace LesCoder\Stream\Lexical;


use Iterator;
use Override;
use LesCoder\Stream\Exception\EndOfStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

final class IteratorLexicalStream extends AbstractLexicalStream
{
    /**
     * @param Iterator<Lexical> $iterator
     */
    public function __construct(private readonly Iterator $iterator)
    {}

    #[Override]
    public function current(): Lexical
    {
        $current = $this->iterator->current();

        if (!$current instanceof Lexical) {
            throw new EndOfStream();
        }

        return $current;
    }

    #[Override]
    public function next(): void
    {
        $this->iterator->next();
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->iterator->valid();
    }
}
