<?php
declare(strict_types=1);

namespace LesCoder\Token\Value\List;

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
        public readonly CodeToken $list,
        public readonly CodeToken $index,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [$this->list, $this->index],
        );
    }
}
