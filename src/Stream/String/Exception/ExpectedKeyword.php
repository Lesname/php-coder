<?php
declare(strict_types=1);

namespace LesCoder\Stream\String\Exception;

use Exception;

final class ExpectedKeyword extends Exception
{
    public function __construct(
        public readonly string $expected,
        public readonly string $got,
    ) {
        parent::__construct("Expected keyword '{$expected}' got '{$got}'");
    }
}
