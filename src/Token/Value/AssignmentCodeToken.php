<?php
declare(strict_types=1);

namespace LesCoder\Token\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class AssignmentCodeToken implements CodeToken
{
    use ImportMergerHelper;

    public function __construct(
        public readonly CodeToken $to,
        public readonly CodeToken $value,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [
                $this->to,
                $this->value,
            ],
        );
    }
}
