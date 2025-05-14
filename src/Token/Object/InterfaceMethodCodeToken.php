<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;
use LesCoder\Token\ParameterCodeToken;

/**
 * @psalm-immutable
 */
final class InterfaceMethodCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param non-empty-string $name
     *
     * @param array<ParameterCodeToken> $parameters
     */
    public function __construct(
        public readonly ?string $name,
        public readonly array $parameters = [],
        public readonly ?CodeToken $returns = null,
        public readonly bool $required = true,
        public readonly ?CommentCodeToken $comment = null,
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

        return $this->mergeImportsFromCodeTokens($tokens);
    }
}
