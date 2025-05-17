<?php
declare(strict_types=1);

namespace LesCoder\Token\Block\Angular\For;

use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class Expression
{
    /**
     * @param array<string, string> $reassign
     */
    public function __construct(
        public readonly CodeToken $iterate,
        public readonly string $as,
        public readonly CodeToken $track,
        public readonly array $reassign = []
    ) {}
}
