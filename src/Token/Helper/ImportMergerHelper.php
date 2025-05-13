<?php
declare(strict_types=1);

namespace LesCoder\Token\Helper;

use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
trait ImportMergerHelper
{
    /**
     * @param array<CodeToken> $tokens
     * @param array<string, string> $initial
     *
     * @return array<string, string>
     *
     * @psalm-pure
     */
    protected function mergeImportsFromCodeTokens(array $tokens, array $initial = []): array
    {
        $merged = $initial;

        foreach ($tokens as $token) {
            $merged = array_replace($merged, $token->getImports());
        }

        return $merged;
    }
}
