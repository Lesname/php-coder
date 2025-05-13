<?php
declare(strict_types=1);

namespace LesCoder\Renderer;

use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\FileRendererSpecification;
use LesCoder\Renderer\Specification\Php\InvokeRendererSpecification;
use LesCoder\Renderer\Specification\Php\VariableRendererSpecification;
use LesCoder\Renderer\Specification\Php\Block\IfRendererSpecification;
use LesCoder\Renderer\Specification\Php\AttributeRendererSpecification;
use LesCoder\Renderer\Specification\Php\ParameterRendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\FloatRendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\EnumRendererSpecification;
use LesCoder\Renderer\Specification\Php\Hint\BuiltInRendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\ClassRendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\StringRendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\AccessRendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\IntegerRendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\InterfaceRendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\AssignmentRendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\CollectionRendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\NamespaceRendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\ClassStringRendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\ClassMethodRendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\ClassPropertyRendererSpecification;
use LesCoder\Renderer\Specification\Php\Value\AnonymousFunctionRenderSpecification;
use LesCoder\Renderer\Specification\Php\Object\InterfaceMethodRendererSpecification;

/**
 * @psalm-immutable
 */
final class PhpSpecificationCodeRenderer extends AbstractGeneralSpecificationCodeRenderer
{
    /**
     * @param array<RendererSpecification> $specifications
     */
    public function __construct(array $specifications = [])
    {
        parent::__construct(
            [
                ...$specifications,
                new AttributeRendererSpecification(),
                new FileRendererSpecification(),
                new InvokeRendererSpecification(),
                new ParameterRendererSpecification(),
                new VariableRendererSpecification(),
                // Block
                new IfRendererSpecification(),
                // Hint
                new BuiltInRendererSpecification(),
                // Object
                new AccessRendererSpecification(),
                new ClassMethodRendererSpecification(),
                new ClassPropertyRendererSpecification(),
                new ClassRendererSpecification(),
                new EnumRendererSpecification(),
                new InterfaceMethodRendererSpecification(),
                new InterfaceRendererSpecification(),
                new NamespaceRendererSpecification(),
                // Value
                new AnonymousFunctionRenderSpecification(),
                new AssignmentRendererSpecification(),
                new Specification\Php\Value\BuiltInRendererSpecification(),
                new ClassStringRendererSpecification(),
                new CollectionRendererSpecification(),
                new Specification\Php\Value\DictionaryRendererSpecification(),
                new FloatRendererSpecification(),
                new IntegerRendererSpecification(),
                new StringRendererSpecification(),
                // Dictionary
                new Specification\Php\Value\Dictionary\AccessRendererSpecification(),
                // List
                new Specification\Php\Value\List\AccessRendererSpecification(),
            ],
        );
    }
}
