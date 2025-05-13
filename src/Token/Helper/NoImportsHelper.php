<?php
declare(strict_types=1);

namespace LesCoder\Token\Helper;

use Override;

/**
 * @psalm-immutable
 */
trait NoImportsHelper
{
    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return [];
    }
}
