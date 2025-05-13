<?php
declare(strict_types=1);

namespace LesCoder\Token;

use Override;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
abstract class AbstractCollectionCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $items
     */
    public function __construct(public readonly array $items)
    {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens($this->items);
    }
}
