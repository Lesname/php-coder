<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use RuntimeException;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Expression\ExportCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;

final class ExportParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $interfaceParseSpecification,
        private readonly ParseSpecification $constantParseSpecification,
        private readonly ParseSpecification $classParseSpecification,
        private readonly ParseSpecification $typeParseSpecification,
    ) {}

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isKeyword($stream, 'export');
    }

    /**
     * @throws Exception\UnexpectedEnd
     * @throws Exception\UnexpectedLabel
     * @throws Exception\UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectKeyword($stream, 'export');
        $stream->next();

        $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);

        foreach ($this->getSubParseSpecifications() as $subParseSpecification) {
            if ($subParseSpecification->isSatisfiedBy($stream)) {
                return new ExportCodeToken($subParseSpecification->parse($stream, $file));
            }
        }

        throw new RuntimeException("No sub parse specification for '{$stream->current()}'");
    }

    /**
     * @return iterable<ParseSpecification>
     */
    private function getSubParseSpecifications(): iterable
    {
        yield $this->classParseSpecification;

        yield $this->interfaceParseSpecification;

        yield $this->constantParseSpecification;

        yield $this->typeParseSpecification;

        yield new DeclareParseSpecification(
            $this->interfaceParseSpecification,
            $this->constantParseSpecification,
            $this->classParseSpecification,
            $this->typeParseSpecification,
        );
    }
}
