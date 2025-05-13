<?php
declare(strict_types=1);

namespace LesCoder\Token\Hint;

use Override;
use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class ReferenceCodeToken implements CodeToken
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $from = null,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        if ($this->from) {
            return [$this->name => $this->from];
        }

        return [];
    }
}
