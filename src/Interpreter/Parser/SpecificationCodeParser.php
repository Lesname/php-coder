<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Stream\Exception\EndOfStream;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Stream\CodeToken\IteratorCodeTokenStream;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Exception\NoParseSpecification;

final class SpecificationCodeParser implements CodeParser
{
    public const int FLAG_SKIP_WHITESPACE = 1;
    public const int FLAG_SKIP_COMMENT = 2;

    private readonly int $flags;

    /**
     * @param non-empty-array<ParseSpecification> $specifications
     */
    public function __construct(
        private readonly array $specifications,
        bool $skipWhitespace = false,
        int $flags = 0,
    ) {
        if ($skipWhitespace) {
            $flags |= self::FLAG_SKIP_WHITESPACE;

            trigger_error("Skip whitespace is moved onto a general flag option", E_USER_DEPRECATED);
        }

        $this->flags = $flags;
    }

    #[Override]
    public function parse(LexicalStream $stream, ?string $file): CodeTokenStream
    {
        $skip = [];

        if ($this->flags & 1) {
            $skip[] = WhitespaceLexical::TYPE;
        }

        if ($this->flags & 1) {
            $skip[] = CommentLexical::TYPE;
        }

        return new IteratorCodeTokenStream(
            (function () use ($stream, $file, $skip) {
                while ($stream->isActive()) {
                    yield $this->subParse($stream, $file);

                    if (count($skip) > 0) {
                        $stream->skip(...$skip);
                    }
                }
            })(),
        );
    }

    /**
     * @throws NoParseSpecification
     * @throws EndOfStream
     */
    private function subParse(LexicalStream $stream, ?string $file): CodeToken
    {
        foreach ($this->specifications as $specification) {
            if ($specification->isSatisfiedBy($stream)) {
                return $specification->parse($stream, $file);
            }
        }

        throw new NoParseSpecification($stream->current());
    }
}
