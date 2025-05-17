<?php
declare(strict_types=1);

namespace LesCoder\Token\Block;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Block\Switch\CaseItem;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class SwitchCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param CodeToken $expression
     * @param array<CaseItem> $cases
     * @param array<CodeToken> $default
     */
    public function __construct(
        public readonly CodeToken $expression,
        public readonly array $cases,
        public readonly array $default,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        $imports = $this->mergeImportsFromCodeTokens(
            $this->default,
            $this->expression->getImports(),
        );

        foreach ($this->cases as $case) {
            $imports = array_replace($imports, $case->getImports());
        }

        return $imports;
    }
}
