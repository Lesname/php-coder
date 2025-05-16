<?php
declare(strict_types=1);

namespace LesCoder\Stream\Lexical;

use Override;
use LesCoder\Stream\Exception\EndOfStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

final class ArrayLexicalStream extends AbstractLexicalStream
{
    private int $position = 0;

    /**
     * @param array<Lexical> $array
     */
    public function __construct(private readonly array $array)
    {}

    #[Override]
    public function current(): Lexical
    {
        if ($this->isEnd()) {
            throw new EndOfStream();
        }

        return $this->array[$this->position];
    }

    #[Override]
    public function next(): void
    {
        $this->position += 1;
    }

    #[Override]
    public function isActive(): bool
    {
        return count($this->array) > $this->position;
    }
}
