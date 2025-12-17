<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class EnumCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<string, CodeToken> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly array $options,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens($this->options);
    }
}
