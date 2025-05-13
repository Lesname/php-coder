<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use Override;
use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class TypeGuardCodeToken implements CodeToken
{
    public function __construct(
        public readonly string $variable,
        public readonly CodeToken $is,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->is->getImports();
    }
}
