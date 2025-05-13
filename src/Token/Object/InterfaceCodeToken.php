<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

use Override;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\ImportMergerHelper;
use LesCoder\Token\Hint\GenericParameterCodeToken;

/**
 * @psalm-immutable
 */
final class InterfaceCodeToken implements CodeToken
{
    use ImportMergerHelper;

    /**
     * @param string $name
     * @param array<CodeToken> $extends
     * @param array<AttributeCodeToken> $attributes
     * @param array<InterfacePropertyCodeToken> $properties
     * @param array<InterfaceMethodCodeToken> $methods
     * @param array<GenericParameterCodeToken> $generics
     */
    public function __construct(
        public readonly string $name,
        public readonly array $extends = [],
        public readonly array $attributes = [],
        public readonly array $properties = [],
        public readonly array $methods = [],
        public readonly array $generics = [],
    ) {}

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getImports(): array
    {
        return $this->mergeImportsFromCodeTokens(
            array_merge(
                $this->extends,
                $this->attributes,
                $this->properties,
                $this->methods,
            ),
        );
    }
}
