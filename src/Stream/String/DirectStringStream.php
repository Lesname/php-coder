<?php
declare(strict_types=1);

namespace LesCoder\Stream\String;

use Override;

final class DirectStringStream extends AbstractStringStream
{
    private readonly int $size;

    private int $position = 0;

    public function __construct(private string $input)
    {
        $this->size = mb_strlen($input);
    }

    /**
     * @param int<1, max> $length
     */
    #[Override]
    public function current(int $length = 1): string
    {
        return mb_substr($this->input, $this->position, $length);
    }

    /**
     * @param int<1, max> $size
     */
    #[Override]
    public function next(int $size = 1): void
    {
        $this->position += $size;
    }

    #[Override]
    public function isEnd(): bool
    {
        return $this->position >= $this->size;
    }
}
