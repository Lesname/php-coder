<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Helper;

/**
 * @psalm-immutable
 */
trait ReservedKeywordHelper
{
    /**
     * @psalm-pure
     */
    private function isReservedKeyword(string $keyword): bool
    {
        return in_array(
            strtolower($keyword),
            [
                'for',
            ],
            true,
        );
    }
}
