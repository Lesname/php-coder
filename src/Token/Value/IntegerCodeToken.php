<?php
declare(strict_types=1);

namespace LesCoder\Token\Value;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\NoImportsHelper;

/**
 * @psalm-immutable
 */
final class IntegerCodeToken implements CodeToken
{
    use NoImportsHelper;

    public function __construct(public readonly int $value)
    {}
}
