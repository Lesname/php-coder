<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class InitiateCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param CodeToken $initiated
     * @param array<CodeToken> $parameters
     */
    public function __construct(
        public readonly CodeToken $initiated,
        public readonly array $parameters,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            array_merge(
                [$this->initiated],
                $this->parameters,
            ),
        );
    }
}
