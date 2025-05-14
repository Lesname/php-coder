<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Stream\CodeToken\IteratorCodeTokenStream;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Exception\NoParseSpecification;

final class SpecificationCodeParser implements CodeParser
{
    /**
     * @param non-empty-array<ParseSpecification> $specifications
     */
    public function __construct(private readonly array $specifications)
    {
    }

    #[Override]
    public function parse(LexicalStream $stream, ?string $file): CodeTokenStream
    {
        return new IteratorCodeTokenStream(
            (function () use ($stream, $file) {
                while ($stream->isActive()) {
                    yield $this->subParse($stream, $file);
                }
            })(),
        );
    }

    private function subParse(LexicalStream $stream, ?string $file): CodeToken
    {
        foreach ($this->specifications as $specification) {
            if ($specification->isSatisfiedBy($stream)) {
                return $specification->parse($stream, $file);
            }
        }

        throw new NoParseSpecification();
    }
}
