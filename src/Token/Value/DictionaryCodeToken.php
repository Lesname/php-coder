<?php
declare(strict_types=1);

namespace LesCoder\Token\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class DictionaryCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<Dictionary\Item> $items
     */
    public function __construct(public readonly array $items)
    {}

    /**
     * @return array<string, string>
     *
     * @psalm-pure
     */
    #[Override]
    public function getImports(): array
    {
        $subTokens = [];

        foreach ($this->items as $item) {
            $subTokens[] = $item->key;
            $subTokens[] = $item->value;
        }

        return $this->mergeImportsFromCodeTokens($subTokens);
    }
}
