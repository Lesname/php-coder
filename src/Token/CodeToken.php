<?php
declare(strict_types=1);

namespace LesCoder\Token;

/**
 * @psalm-immutable
 */
interface CodeToken
{
    /**
     * @return array<string, string>
     *
     * @psalm-pure
     */
    public function getImports(): array;
}
