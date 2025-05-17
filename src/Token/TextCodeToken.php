<?php
declare(strict_types=1);

namespace LesCoder\Token;

use Override;

/**
 * @psalm-immutable
 */
final class TextCodeToken implements CodeToken
{
    public function __construct(public readonly string $text)
    {}

    #[Override]
    public function getImports(): array
    {
        return [];
    }
}
