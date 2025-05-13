<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer;

use Override;
use Generator;
use LesCoder\Stream\String\StringStream;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Stream\Lexical\IteratorLexicalStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\TextLexical;
use LesCoder\Interpreter\Lexer\Specification\Specification;

final class SpecificationCodeLexer implements CodeLexer
{
    /**
     * @param array<Specification> $specifications
     */
    public function __construct(private readonly array $specifications)
    {}

    #[Override]
    public function tokenize(StringStream $stream): LexicalStream
    {
        return new IteratorLexicalStream($this->generate($stream));
    }

    /**
     * @return Generator<Lexical>
     */
    private function generate(StringStream $stream): Generator
    {
        $text = '';

        while ($stream->isActive()) {
            $useSpecification = null;

            foreach ($this->specifications as $specification) {
                if (!$specification->isSatisfiedBy($stream)) {
                    continue;
                }

                $useSpecification = $specification;
                break;
            }

            if ($useSpecification) {
                if ($text) {
                    yield new TextLexical($text);

                    $text = '';
                }

                yield $useSpecification->parse($stream);
            } else {
                $text .= $stream->current();
                $stream->next();
            }
        }

        if ($text) {
            yield new TextLexical($text);
        }
    }
}
