<?php
declare(strict_types=1);

namespace LesCoder\Token\Value\Dictionary;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class Item
{
    use ImportMergerHelper;

    public function __construct(
        public readonly CodeToken $key,
        public readonly CodeToken $value,
    ) {}
}
