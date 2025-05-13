<?php
declare(strict_types=1);

namespace LesCoder\Token\Hint;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class GenericCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $parameters
     */
    public function __construct(
        public readonly CodeToken $base,
        public readonly array $parameters,
    ) {}

    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            $this->parameters,
            $this->base->getImports(),
        );
    }
}
