<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class NamespaceCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $body
     */
    public function __construct(
        public readonly string $name,
        public readonly array $body,
    ) {}

    /**
     * @return array<string, string>
     */
    public function getBodyImports(): array
    {
        return $this->mergeImportsFromCodeTokens($this->body);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return [];
    }
}
