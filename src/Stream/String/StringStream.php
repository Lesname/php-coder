<?php
declare(strict_types=1);

namespace LesCoder\Stream\String;

use Override;
use LesCoder\Stream\Stream;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Stream\String\Exception\ExpectedKeyword;

/**
 * @extends Stream<string>
 */
interface StringStream extends Stream
{
    #[Override]
    public function current(int $length = 1): string;

    public function matchesExactly(string $input): bool;

    /**
     * @throws ExpectedExactly
     */
    public function expectExactly(string $string): void;

    public function matchesKeyword(string $keyword): bool;

    /**
     * @throws ExpectedKeyword
     */
    public function expectKeyword(string $keyword): void;

    public function skipWhitespace(): void;
}
