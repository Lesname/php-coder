<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class ReplaceFailed extends AbstractException
{
    public function __construct(public readonly string $subject)
    {
        parent::__construct("Replace failed on '{$subject}'");
    }
}
