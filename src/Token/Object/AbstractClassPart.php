<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
abstract class AbstractClassPart implements CodeToken
{
    public const int FLAG_OVERRIDE = 1;
    public const int FLAG_STATIC = 2;
    public const int FLAG_READONLY = 4;
    public const int FLAG_OPTIONAL = 8;

    /**
     * @param array<AttributeCodeToken> $attributes
     */
    public function __construct(
        public readonly Visibility $visibility,
        public readonly string $name,
        public readonly int $flags,
        public readonly array $attributes,
        public readonly ?CommentCodeToken $comment,
    ) {}
}
