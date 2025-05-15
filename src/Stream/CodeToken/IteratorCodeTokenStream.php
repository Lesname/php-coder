<?php
declare(strict_types=1);

namespace LesCoder\Stream\CodeToken;

use Iterator;
use Override;
use RuntimeException;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\AbstractStream;

/**
 * @extends AbstractStream<CodeToken>
 */
final class IteratorCodeTokenStream extends AbstractStream implements CodeTokenStream
{
    /**
     * @param Iterator<CodeToken> $iterator
     */
    public function __construct(private readonly Iterator $iterator)
    {}

    #[Override]
    public function current(): CodeToken
    {
        $current = $this->iterator->current();

        if (!$current instanceof CodeToken) {
            throw new RuntimeException();
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
