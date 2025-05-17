<?php
declare(strict_types=1);

namespace LesCoder\Token\Value\Dictionary;

use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class Item
{
    public function __construct(
        public readonly CodeToken $key,
        public readonly CodeToken $value,
    ) {}
}
