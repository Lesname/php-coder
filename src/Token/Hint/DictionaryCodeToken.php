<?php
declare(strict_types=1);

namespace LesCoder\Token\Hint;

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
     * @param array<array{key: CodeToken, value: CodeToken, required: boolean, comment?: string | null}> $items
     */
    public function __construct(public readonly array $items)
    {}

    #[Override]
    public function getImports(): array
    {
        /** @var CodeToken[] $tokens */
        $tokens = [];

        foreach ($this->items as $item) {
            $tokens[] = $item['key'];
            $tokens[] = $item['value'];
        }

        return $this->mergeImportsFromCodeTokens($tokens);
    }
}
