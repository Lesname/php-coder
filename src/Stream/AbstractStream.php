<?php
declare(strict_types=1);

namespace LesCoder\Stream;

use Override;

/**
 * @implements Stream<T>
 *
 * @template T
 */
abstract class AbstractStream implements Stream
{
    #[Override]
    public function isEnd(): bool
    {
        return !$this->isActive();
    }
}
