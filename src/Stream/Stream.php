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
    public function current(int $length = 1): mixed;

    public function next(int $size = 1): void;

    public function isActive(): bool;

    public function isEnd(): bool;
}
