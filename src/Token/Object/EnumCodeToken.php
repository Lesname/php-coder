<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;

/**
 * @psalm-immutable
 */
final class EnumCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<string> | array<string, CodeToken> $cases
     * @param array<CodeToken> $implements
     * @param array<CodeToken> $uses
     */
    public function __construct(
        public readonly string $name,
        public readonly array $cases,
        public readonly ?CodeToken $backs = null,
        public readonly array $implements = [],
        public readonly array $uses = [],
        public readonly ?CommentCodeToken $comment = null,
    ) {}

    #[Override]
    public function getImports(): array
    {
        $filtered = [];

        foreach ($this->cases as $case) {
            if ($case instanceof CodeToken) {
                $filtered[] = $case;
            }
        }

        return $this->mergeImportsFromCodeTokens(
            array_merge(
                $filtered,
                $this->implements,
                $this->uses,
            ),
            $this->backs
                ? $this->backs->getImports()
                : [],
        );
    }
}
