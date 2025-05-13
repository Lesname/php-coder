<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;
use LesCoder\Token\Hint\GenericParameterCodeToken;

/**
 * @psalm-immutable
 */
final class ClassCodeToken implements CodeToken
{
    use ImportMergerHelper;

    public const FLAG_ABSTRACT = 1;
    public const FLAG_FINAL = 2;
    public const FLAG_READONLY = 4;

    /**
     * @param array<CodeToken> $implements
     * @param array<AttributeCodeToken> $attributes
     * @param array<ClassPropertyCodeToken|ClassGetPropertyCodeToken|ClassSetPropertyCodeToken> $properties
     * @param array<ClassMethodCodeToken> $methods
     * @param array<GenericParameterCodeToken> $generics
     */
    public function __construct(
        public readonly string $name,
        public readonly ?CodeToken $extends = null,
        public readonly array $implements = [],
        public readonly array $attributes = [],
        public readonly array $properties = [],
        public readonly array $methods = [],
        public readonly int $flags = 0,
        public readonly array $generics = [],
        public readonly ?CommentCodeToken $comment = null,
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        $tokens = $this->extends
            ? [$this->extends]
            : [];

        return $this->mergeImportsFromCodeTokens(
            array_merge(
                $tokens,
                $this->implements,
                $this->attributes,
                $this->properties,
                $this->methods,
            ),
        );
    }
}
