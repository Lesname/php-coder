<?php
declare(strict_types=1);

namespace LesCoder\Token;

use Override;

/**
 * @psalm-immutable
 */
abstract class AbstractNestedCodeToken implements CodeToken
{
    public function __construct(public readonly CodeToken $code)
    {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->code->getImports();
    }
}
