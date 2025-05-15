<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Hint\GenericCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;

final class ReferenceParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    /**
     * @param array<string, string> $imports
     */
    public function __construct(
        private readonly array $imports,
        private ?ParseSpecification $hintParseSpecification = null,
    ) {}

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isLexical($stream, LabelLexical::TYPE);
    }

    /**
     * @throws Exception\UnexpectedEnd
     * @throws Exception\UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectLexical($stream, LabelLexical::TYPE);
        $refName = (string)$stream->current();

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $from = $this->imports[$refName] ?? null;

        if ($file !== null && $from !== null && str_starts_with($from, '.')) {
            $from = str_starts_with($from, '../')
                ? dirname($file) . "/{$from}"
                : dirname($file) . substr($from, 1);
        }

        $reference = new ReferenceCodeToken($refName, $from);

        if (!$this->isLexical($stream, LowerThanLexical::TYPE)) {
            return $reference;
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $genericParameters = [$this->getHintParseSpecification()->parse($stream, $file)];

        while ($stream->isActive() && $this->isLexical($stream, CommaLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $genericParameters[] = $this->getHintParseSpecification()->parse($stream, $file);
        }

        $this->expectLexical($stream, GreaterThanLexical::TYPE);
        $stream->next();

        return new GenericCodeToken($reference, $genericParameters);
    }

    private function getHintParseSpecification(): ParseSpecification
    {
        return $this->hintParseSpecification ??= new HintParseSpecification($this);
    }
}
