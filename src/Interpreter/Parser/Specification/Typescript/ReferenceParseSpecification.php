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
     * @param array<string, array{from: string, name: string}> $imports
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

        if (isset($this->imports[$refName])) {
            $import = $this->imports[$refName];

            if ($file !== null && str_starts_with($import['from'], '.')) {
                $from = str_starts_with($import['from'], '../')
                    ? dirname($file) . "/{$import['from']}"
                    : dirname($file) . substr($import['from'], 1);
            } else {
                $from = $import['from'];
            }

            $reference = new ReferenceCodeToken($import['name'], $from);
        } else {
            $reference = new ReferenceCodeToken($refName, $file);
        }

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
