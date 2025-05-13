<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Hint\Exception;

use JetBrains\PhpStorm\Pure;
use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class NotSupported extends AbstractException
{
    public function __construct()
    {
        parent::__construct('Not supported ');
    }
}
