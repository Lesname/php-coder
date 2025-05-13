<?php
declare(strict_types=1);

namespace LesCoder\Token;

use LesCoder\Token\Helper\NoImportsHelper;

/**
 * @psalm-immutable
 */
final class ConstantCodeToken implements CodeToken
{
    use NoImportsHelper;

    public function __construct(public readonly string $name)
    {}
}
