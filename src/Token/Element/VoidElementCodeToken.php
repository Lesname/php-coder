<?php
declare(strict_types=1);

namespace LesCoder\Token\Element;

use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class VoidElementCodeToken extends AbstractElementCodeToken
{
    /**
     * @param array<string, CodeToken> $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly array $attributes,
    ) {}
}
