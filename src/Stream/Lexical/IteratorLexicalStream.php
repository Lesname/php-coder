<?php
declare(strict_types=1);

namespace LesCoder\Stream\Lexical;


use Iterator;
use Override;
use LesCoder\Stream\Exception\EndOfStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

final class IteratorLexicalStream extends AbstractLexicalStream
{
    /** @var array<Lexical>  */
    private array $lookahead = [];

    private Lexical|null $active = null;

    /**
     * @param Iterator<Lexical> $iterator
     */
    public function __construct(private readonly Iterator $iterator)
    {}

    #[Override]
    public function current(): Lexical
    {
        if ($this->active) {
            return $this->active;
        }

        $current = $this->iterator->current();

        if (!$current instanceof Lexical) {
            throw new EndOfStream();
        }

        return $current;
    }

    /**
     * @param positive-int $step
     */
    public function lookahead(int $step = 1): ?Lexical
    {
        if ($this->active === null) {
            $this->active = $this->iterator->current();
        }

        while (!isset($this->lookahead[$step - 1]) && $this->iterator->valid()) {
            $this->iterator->next();

            $token = $this->iterator->current();

            if ($token) {
                $this->lookahead[] = $token;
            }
        }

        return $this->lookahead[$step - 1] ?? null;
    }

    #[Override]
    public function next(): void
    {
        $this->active = null;

        if (count($this->lookahead) > 0) {
            $this->active = array_shift($this->lookahead);
        } else {
            $this->iterator->next();
        }
    }

    #[Override]
    public function isActive(): bool
    {
        return count($this->lookahead) > 0
            || $this->active !== null
            || $this->iterator->valid();
    }
}
