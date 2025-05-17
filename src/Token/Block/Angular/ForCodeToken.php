<?php
declare(strict_types=1);

namespace LesCoder\Token\Block\Angular;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;
use LesCoder\Token\Block\Angular\For\Expression;

/**
 * @psalm-immutable
 */
final class ForCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<CodeToken> $body
     * @param array<CodeToken> $empty
     */
    public function __construct(
        public readonly Expression $expression,
        public readonly array $body,
        public readonly array $empty = [],
    ) {}

    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            [
                $this->expression->track,
                ...$this->body,
                ...$this->empty,
            ],
            $this->expression->iterate->getImports(),
        );
    }
}
