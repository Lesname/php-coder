<?php
declare(strict_types=1);

namespace LesCoder\Token\Block;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class IfCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param CodeToken $expression
     * @param array<CodeToken> $truthy
     * @param array<CodeToken> $falsey
     */
    public function __construct(
        public readonly CodeToken $expression,
        public readonly array $truthy = [],
        public readonly array $falsey = [],
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [...$this->truthy, ...$this->falsey],
            $this->expression->getImports(),
        );
    }
}
