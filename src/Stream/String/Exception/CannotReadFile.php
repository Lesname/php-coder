<?php

declare(strict_types=1);

namespace LesCoder\Stream\String\Exception;

use Exception;

final class CannotReadFile extends Exception
{
    public function __construct(
        public readonly string $filename,
    ) {
        parent::__construct("Cannot read file '{$filename}'");
    }
}
