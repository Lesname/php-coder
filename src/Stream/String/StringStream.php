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
    /**
     * @param int<1, max> $length
     */
    #[Override]
    public function current(int $length = 1): string;

    /**
     * @param int<1, max> $size
     */
    #[Override]
    public function next(int $size = 1): void;

    /**
     * @param non-empty-string $input
     */
    public function matchesExactly(string $input): bool;

    /**
     * @param non-empty-string $input
     *
     * @throws ExpectedExactly
     */
    public function expectExactly(string $input): void;

    /**
     * @param non-empty-string $keyword
     */
    public function matchesKeyword(string $keyword): bool;

    /**
     * @param non-empty-string $keyword
     *
     * @throws ExpectedKeyword
     */
    public function expectKeyword(string $keyword): void;

    public function skipWhitespace(): void;
}
