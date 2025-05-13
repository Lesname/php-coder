<?php
declare(strict_types=1);

namespace LesCoder\Renderer;

use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\FileRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\InvokeRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\VariableRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Block\IfRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\AttributeRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\ParameterRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Value\FloatRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Hint\BuiltInRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Hint\GenericRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\ClassRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Value\StringRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\AccessRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Value\IntegerRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Hint\DictionaryRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\InterfaceRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Value\AssignmentRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Value\CollectionRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\ExportRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\DeclareRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\ClassMethodRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\DowncastRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Hint\IndexSignatureRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\TypeGuardRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\ClassPropertyRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Value\AnonymousFunctionRenderSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\InterfaceMethodRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\ClassGetPropertyRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\InterfacePropertyRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\TypeDeclarationRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Expression\VariableDeclarationRendererSpecification;

/**
 * @psalm-immutable
 */
final class TypeScriptSpecificationCodeRenderer extends AbstractGeneralSpecificationCodeRenderer
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
                // Expression
                new DeclareRendererSpecification(),
                new DowncastRendererSpecification(),
                new ExportRendererSpecification(),
                new TypeDeclarationRendererSpecification(),
                new TypeGuardRendererSpecification(),
                new VariableDeclarationRendererSpecification(),
                // Hint
                new BuiltInRendererSpecification(),
                new DictionaryRendererSpecification(),
                new GenericRendererSpecification(),
                new IndexSignatureRendererSpecification(),
                // Object
                new AccessRendererSpecification(),
                new ClassGetPropertyRendererSpecification(),
                new ClassMethodRendererSpecification(),
                new ClassPropertyRendererSpecification(),
                new ClassRendererSpecification(),
                new InterfaceMethodRendererSpecification(),
                new InterfacePropertyRendererSpecification(),
                new InterfaceRendererSpecification(),
                // Value
                new AnonymousFunctionRenderSpecification(),
                new AssignmentRendererSpecification(),
                new Specification\Typescript\Value\BuiltInRendererSpecification(),
                new CollectionRendererSpecification(),
                new Specification\Typescript\Value\DictionaryRendererSpecification(),
                new FloatRendererSpecification(),
                new IntegerRendererSpecification(),
                new StringRendererSpecification(),
                // Dictionary
                new Specification\Typescript\Value\Dictionary\AccessRendererSpecification(),
                // List
                new Specification\Typescript\Value\List\AccessRendererSpecification(),
            ],
        );
    }
}
