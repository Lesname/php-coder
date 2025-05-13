<?php
declare(strict_types=1);

namespace LesCoder\Token\Value\Dictionary;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class AccessCodeToken implements CodeToken
{
    use ImportMergerHelper;

    public function __construct(
        public readonly CodeToken $dictionary,
        public readonly CodeToken $key,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [$this->dictionary, $this->key],
        );
    }
}
