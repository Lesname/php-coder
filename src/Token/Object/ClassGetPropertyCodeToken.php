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
final class ClassGetPropertyCodeToken extends AbstractClassPart
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $body
     * @param array<AttributeCodeToken> $attributes
     */
    public function __construct(
        Visibility $visibility,
        string $name,
        public readonly ?CodeToken $hint = null,
        public readonly array $body = [],
        int $flags = 0,
        array $attributes = [],
        ?CommentCodeToken $comment = null,
    ) {
        parent::__construct($visibility, $name, $flags, $attributes, $comment);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        $tokens = [
            ...$this->body,
            ...$this->attributes,
        ];

        if (isset($this->hint)) {
            $tokens[] = $this->hint;
        }

        return $this->mergeImportsFromCodeTokens($tokens);
    }
}
