<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class AccessCodeToken implements CodeToken
{
    use ImportMergerHelper;

    public const FLAG_STATIC = 1;
    public const FLAG_NULLABLE = 2;

    public function __construct(
        public readonly CodeToken $called,
        public readonly CodeToken $property,
        public readonly int $flags = 0,
    ) {}

    public function isNullable(): bool
    {
        return $this->hasFlags(self::FLAG_NULLABLE);
    }

    public function isStatic(): bool
    {
        return $this->hasFlags(self::FLAG_STATIC);
    }

    public function hasFlags(int $flags): bool
    {
        return ($this->flags & $flags) === $flags;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [$this->called, $this->property],
        );
    }
}
