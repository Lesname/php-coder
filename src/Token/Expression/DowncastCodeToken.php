<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class DowncastCodeToken implements CodeToken
{
    use ImportMergerHelper;

    public function __construct(
        public readonly CodeToken $expression,
        public readonly CodeToken $as,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [$this->expression, $this->as],
        );
    }
}
