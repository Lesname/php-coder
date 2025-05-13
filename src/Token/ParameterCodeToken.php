<?php
declare(strict_types=1);

namespace LesCoder\Token;

use Override;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class ParameterCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param string $name
     * @param CodeToken|null $hint
     * @param CodeToken|null $assigned
     * @param array<AttributeCodeToken> $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly ?CodeToken $hint = null,
        public readonly ?CodeToken $assigned = null,
        public readonly array $attributes = [],
        public readonly bool $optional = false,
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

        if (isset($this->assigned)) {
            $tokens[] = $this->assigned;
        }

        return $this->mergeImportsFromCodeTokens($tokens);
    }
}
