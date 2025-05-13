<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class TernaryCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param CodeToken $expression
     * @param CodeToken $truthy
     * @param CodeToken $falsey
     */
    public function __construct(
        public readonly CodeToken $expression,
        public readonly CodeToken $truthy,
        public readonly CodeToken $falsey,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [
                $this->expression,
                $this->truthy,
                $this->falsey,
            ],
        );
    }
}
