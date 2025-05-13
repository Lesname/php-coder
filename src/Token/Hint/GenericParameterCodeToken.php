<?php
declare(strict_types=1);

namespace LesCoder\Token\Hint;

use Override;
use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
final class GenericParameterCodeToken implements CodeToken
{
    public function __construct(
        public readonly CodeToken $reference,
        public readonly ?CodeToken $extends = null,
        public readonly ?CodeToken $assigned = null,
    ) {}

    /**
     * @return array<string, string>
     *
     * @psalm-pure
     */
    #[Override]
    public function getImports(): array
    {
        $imports = $this->reference->getImports();

        if ($this->extends) {
            $imports = array_replace($imports, $this->extends->getImports());
        }

        if ($this->assigned) {
            $imports = array_replace($imports, $this->assigned->getImports());
        }

        return $imports;
    }
}
