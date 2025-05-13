<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class InterfacePropertyCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<AttributeCodeToken> $attributes
     */
    public function __construct(
        public readonly CodeToken $name,
        public readonly ?CodeToken $hint = null,
        public readonly array $attributes = [],
        public readonly ?CommentCodeToken $comment = null,
        public readonly bool $required = true,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        $tokens = $this->attributes;

        if (isset($this->hint)) {
            $tokens[] = $this->hint;
        }

        return $this->mergeImportsFromCodeTokens($tokens);
    }
}
