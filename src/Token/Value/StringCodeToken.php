<?php
declare(strict_types=1);

namespace LesCoder\Token\Value;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\NoImportsHelper;

/**
 * @psalm-immutable
 */
final class StringCodeToken implements CodeToken
{
    use NoImportsHelper;

    public function __construct(public readonly string $value)
    {}
}
