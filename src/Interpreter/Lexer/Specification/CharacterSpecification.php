<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Interpreter\Lexer\Lexical\Character\AbstractCharacterLexical;

final class CharacterSpecification implements Specification
{
    /**
     * @param class-string<AbstractCharacterLexical> $lexicon
     */
    public function __construct(
        private readonly string $character,
        private readonly string $lexicon,
    ) {
        assert(mb_strlen($this->character) === 1);
    }

    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return $this->character === $code->current();
    }

    /**
     * @throws ExpectedExactly
     */
    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $lexical = new $this->lexicon();
        $code->expectExactly($this->character);

        return $lexical;
    }
}
