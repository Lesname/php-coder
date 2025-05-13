<?php
declare(strict_types=1);

namespace LesCoder\Token\Value;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;
use LesCoder\Token\ParameterCodeToken;

/**
 * @psalm-immutable
 */
final class AnonymousFunctionCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param array<ParameterCodeToken> $parameters
     * @param CodeToken|null $returns
     * @param array<CodeToken> $body
     */
    public function __construct(
        public readonly array $parameters = [],
        public readonly ?CodeToken $returns = null,
        public readonly array $body = [],
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        $tokens = $this->parameters;

        if (isset($this->returns)) {
            $tokens[] = $this->returns;
        }

        $tokens = array_merge($tokens, $this->body);

        return $this->mergeImportsFromCodeTokens($tokens);
    }
}
