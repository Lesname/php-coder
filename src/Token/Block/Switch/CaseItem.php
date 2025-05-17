<?php
declare(strict_types=1);

namespace LesCoder\Token\Block\Switch;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class CaseItem
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $body
     */
    public function __construct(
        public readonly CodeToken $when,
        public readonly array $body,
    ) {}

    /**
     * @return array<string, string>
     *
     * @psalm-pure
     */
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            $this->body,
            $this->when->getImports()
        );
    }
}
