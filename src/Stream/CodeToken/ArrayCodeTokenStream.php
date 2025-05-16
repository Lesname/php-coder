<?php
declare(strict_types=1);

namespace LesCoder\Stream\CodeToken;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\AbstractStream;
use LesCoder\Stream\Exception\EndOfStream;

/**
 * @extends AbstractStream<CodeToken>
 */
final class ArrayCodeTokenStream extends AbstractStream implements CodeTokenStream
{
    private int $position = 0;

    /**
     * @param array<CodeToken> $tokens
     */
    public function __construct(private readonly array $tokens)
    {}

    #[Override]
    public function current(): CodeToken
    {
        if (!isset($this->tokens[$this->position])) {
            throw new EndOfStream();
        }

        return $this->tokens[$this->position];
    }

    #[Override]
    public function next(): void
    {
        $this->position += 1;
    }

    #[Override]
    public function isActive(): bool
    {
        return count($this->tokens) > $this->position;
    }
}
