<?php
declare(strict_types=1);

namespace LesCoder\Token;

use Override;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class FileCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $body
     */
    public function __construct(public readonly array $body)
    {}

    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens($this->body);
    }
}
