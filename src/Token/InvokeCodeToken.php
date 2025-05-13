<?php
declare(strict_types=1);

namespace LesCoder\Token;

use Override;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class InvokeCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param CodeToken $invoke
     * @param array<CodeToken> $parameters
     */
    public function __construct(
        public readonly CodeToken $invoke,
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
                [$this->invoke],
                $this->parameters,
            ),
        );
    }
}
