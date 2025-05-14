<?php
declare(strict_types=1);

namespace LesCoder\Stream;

/**
 * @template T
 */
interface Stream
{
    /**
     * @return T
     */
    public function current(): mixed;

    public function next(): void;

    public function isActive(): bool;

    public function isEnd(): bool;
}
