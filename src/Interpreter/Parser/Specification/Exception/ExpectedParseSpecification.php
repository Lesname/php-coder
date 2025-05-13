<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class ExpectedParseSpecification extends AbstractException
{
    public function __construct(public readonly mixed $gotten)
    {
        $typeGotten = get_debug_type($gotten);

        parent::__construct("Expected parse specification, gotten {$typeGotten}");
    }
}
