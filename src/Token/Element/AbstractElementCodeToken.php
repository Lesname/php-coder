<?php
declare(strict_types=1);

namespace LesCoder\Token\Element;

use Override;
use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
abstract class AbstractElementCodeToken implements CodeToken
{
    #[Override]
    public function getImports(): array
    {
        return [];
    }
}
