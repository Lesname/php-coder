<?php

declare(strict_types=1);

namespace LesCoder\Token\Hint;

use Override;
use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class FunctionCodeToken implements CodeToken
{
    public function __construct(
        public readonly string $name,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return [];
    }
}
