<?php
declare(strict_types=1);

namespace LesCoder\Stream;

use LesCoder\Stream\Exception\EndOfStream;

/**
 * @template T
 */
interface Stream
{
    /**
     * @throws EndOfStream
     *
     * @return T
     */
    public function current(): mixed;

    public function next(): void;

    public function isActive(): bool;

    public function isEnd(): bool;
}
