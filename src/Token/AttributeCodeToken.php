<?php
declare(strict_types=1);

namespace LesCoder\Token;

use Override;
use LesCoder\Token\Helper\ImportMergerHelper;
use LesCoder\Token\Hint\ReferenceCodeToken;

/**
 * @psalm-immutable
 */
final class AttributeCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param ReferenceCodeToken $reference
     * @param array<CodeToken> $parameters
     */
    public function __construct(
        public readonly ReferenceCodeToken $reference,
        public readonly array $parameters = [],
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            $this->parameters,
            $this->reference->getImports(),
        );
    }
}
