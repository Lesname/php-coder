<?php
declare(strict_types=1);

namespace LesCoder\Stream\String;

use Override;

final class DirectStringStream extends AbstractStringStream
{
    public function __construct(private string $input)
    {}

    /**
     * @param int<1, max> $length
     */
    #[Override]
    public function current(int $length = 1): string
    {
        return mb_substr($this->input, 0, $length);
    }

    /**
     * @param int<1, max> $size
     */
    #[Override]
    public function next(int $size = 1): void
    {
        $this->input = mb_substr($this->input, $size);
    }

    #[Override]
    public function isEnd(): bool
    {
        return strlen($this->input) === 0;
    }
}
