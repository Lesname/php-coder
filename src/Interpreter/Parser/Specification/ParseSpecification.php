<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification;

use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;

interface ParseSpecification
{
    public function isSatisfiedBy(LexicalStream $stream): bool;

    public function parse(LexicalStream $stream, ?string $file = null): CodeToken;
}
