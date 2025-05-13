<?php
declare(strict_types=1);

namespace LesCoder\Renderer;

use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\General\LineRendererSpecification;
use LesCoder\Renderer\Specification\General\ReturnRendererSpecification;
use LesCoder\Renderer\Specification\General\CommentRendererSpecification;
use LesCoder\Renderer\Specification\General\Hint\UnionRendererSpecification;
use LesCoder\Renderer\Specification\General\Object\ThrowRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\OrRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\AndRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\NotRendererSpecification;
use LesCoder\Renderer\Specification\General\Hint\ReferenceRendererSpecification;
use LesCoder\Renderer\Specification\General\Object\InitiateRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\GroupRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\TernaryRendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\InterfaceRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\CoalescingRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\ComparisonRendererSpecification;
use LesCoder\Renderer\Specification\General\Expression\CalculationRendererSpecification;

/**
 * @psalm-immutable
 */
abstract class AbstractGeneralSpecificationCodeRenderer extends AbstractSpecificationCodeRenderer
{
    /**
     * @param array<RendererSpecification> $specifications
     */
    public function __construct(array $specifications = [])
    {
        parent::__construct(
            [
                ...$specifications,
                new CommentRendererSpecification(),
                new LineRendererSpecification(),
                new ReturnRendererSpecification(),
                // Expression
                new AndRendererSpecification(),
                new CalculationRendererSpecification(),
                new CoalescingRendererSpecification(),
                new ComparisonRendererSpecification(),
                new GroupRendererSpecification(),
                new NotRendererSpecification(),
                new OrRendererSpecification(),
                new TernaryRendererSpecification(),
                // Hint
                new InterfaceRendererSpecification(),
                new ReferenceRendererSpecification(),
                new UnionRendererSpecification(),
                // Object
                new InitiateRendererSpecification(),
                new ThrowRendererSpecification(),
            ],
        );
    }
}
