<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class FilterCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $parameters
     */
    public function __construct(
        public readonly string $name,
        public readonly CodeToken $expression,
        public readonly array $parameters = [],
    ) {}

    /**
     * @return array<string, string>
     *
     * @psalm-pure
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            $this->parameters,
            $this->expression->getImports(),
        );
    }
}
