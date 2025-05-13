<?php
declare(strict_types=1);

namespace LesCoder\Token\Value;

use Override;
use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class ClassStringCodeToken implements CodeToken
{
    public function __construct(public readonly string $class)
    {}

    /**
     * @return array<string, string>
     *
     * @psalm-pure
     */
    #[Override]
    public function getImports(): array
    {
        return [];
    }
}
