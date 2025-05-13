<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;
use LesCoder\Token\ParameterCodeToken;

/**
 * @psalm-immutable
 */
final class ClassMethodCodeToken extends AbstractClassPart
{
    use ImportMergerHelper;

    /**
     * @param array<ParameterCodeToken|ClassPropertyCodeToken> $parameters
     * @param array<CodeToken> $body
     * @param array<AttributeCodeToken> $attributes
     */
    public function __construct(
        Visibility $visibility,
        string $name,
        public readonly array $parameters = [],
        public readonly ?CodeToken $returns = null,
        public readonly array $body = [],
        int $flags = 0,
        array $attributes = [],
        ?CommentCodeToken $comment = null,
    ) {
        parent::__construct($visibility, $name, $flags, $attributes, $comment);
    }

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

        $tokens = array_merge($tokens, $this->body, $this->attributes);

        return $this->mergeImportsFromCodeTokens($tokens);
    }
}
