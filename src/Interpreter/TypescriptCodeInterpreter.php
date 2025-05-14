<?php
declare(strict_types=1);

namespace LesCoder\Interpreter;

use Override;
use Generator;
use RuntimeException;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\String\StringStream;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Interpreter\Lexer\CodeLexer;
use LesCoder\Interpreter\Parser\CodeParser;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Stream\CodeToken\IteratorCodeTokenStream;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Parser\SpecificationCodeParser;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\AsteriskLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Parser\Specification\GroupParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\HintParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\TypeParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\ClassParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Parser\Specification\Typescript\ExportParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\DeclareParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLabel;
use LesCoder\Interpreter\Parser\Specification\Exception\ExpectedParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\ConstantParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Parser\Specification\Typescript\InterfaceParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\ReferenceParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\AttributeParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\ExpressionParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;

final class TypescriptCodeInterpreter implements CodeInterpreter
{
    use ExpectParseSpecificationHelper;

    private ?CodeLexer $codeLexer;

    public function __construct(?CodeLexer $codeLexer = null)
    {
        $this->codeLexer = $codeLexer;
    }

    /**
     * @throws ExpectedParseSpecification
     * @throws UnexpectedEnd
     * @throws UnexpectedLabel
     * @throws UnexpectedLexical
     */
    #[Override]
    public function interpret(StringStream $stream, ?string $file = null): CodeTokenStream
    {
        $lexicals = $this->getCodeLexer()->tokenize($stream);
        $lexicals->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        /** @var array<string, string> $imports */
        $imports = [];

        while ($lexicals->isActive()) {
            $current = $lexicals->current();

            if (!$current instanceof LabelLexical || (string)$current !== 'import') {
                break;
            }

            $imports = array_replace($imports, $this->parseImport($lexicals));
            $lexicals->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return $this->getCodeParser($imports)->parse($lexicals, $file);
    }

    /**
     * @return array<string, string>
     *
     * @throws UnexpectedLexical
     * @throws UnexpectedEnd
     * @throws UnexpectedLabel
     */
    private function parseImport(LexicalStream $stream): array
    {
        $this->expectKeyword($stream, 'import');
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, AsteriskLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->expectKeyword($stream, 'as');

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->expectLexical($stream, LabelLexical::TYPE);
            $name = (string)$stream->current();

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->expectKeyword($stream, 'from');
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->expectLexical($stream, StringLexical::TYPE);

            $from = (string)$stream->current();
            $stream->next();

            if ($stream->current()->getType() === SemicolonLexical::TYPE) {
                $stream->next();
            }

            return [$name => $from];
        }

        if ($stream->current()->getType() !== CurlyBracketLeftLexical::TYPE) {
            throw new RuntimeException("Expected `{` for '{$stream->current()->getType()}'");
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $names = [];

        while ($stream->isActive()) {
            if ($stream->current()->getType() !== LabelLexical::TYPE) {
                throw new RuntimeException("Expected label, got " . $stream->current()->getType());
            }

            $names[] = (string)$stream->current();

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($stream->current()->getType() !== CommaLexical::TYPE) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($stream->current()->getType() === CurlyBracketRightLexical::TYPE) {
                break;
            }
        }

        if ($stream->current()->getType() !== CurlyBracketRightLexical::TYPE) {
            throw new RuntimeException();
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($stream->current()->getType() !== LabelLexical::TYPE) {
            throw new RuntimeException();
        }

        if ((string)$stream->current() !== 'from') {
            throw new RuntimeException();
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($stream->current()->getType() !== StringLexical::TYPE) {
            throw new RuntimeException();
        }

        $from = (string)$stream->current();
        $stream->next();

        if ($stream->current()->getType() === SemicolonLexical::TYPE) {
            $stream->next();
        }

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $imports = [];

        foreach ($names as $name) {
            $imports[$name] = $from;
        }

        return $imports;
    }

    /**
     * @param array<string, string> $imports
     *
     * @throws ExpectedParseSpecification
     */
    private function getCodeParser(array $imports): CodeParser
    {
        $referenceParseSpecification = new ReferenceParseSpecification($imports);
        $hintParseSpecification = new HintParseSpecification($referenceParseSpecification);

        $expressionParseSpecification = new ExpressionParseSpecification($referenceParseSpecification, $hintParseSpecification, $imports);

        return new SpecificationCodeParser(
            [
                new GroupParseSpecification(
                    [
                        new InterfaceParseSpecification($hintParseSpecification, $expressionParseSpecification),
                        new ConstantParseSpecification($expressionParseSpecification, $hintParseSpecification),
                        new ClassParseSpecification(
                            new AttributeParseSpecification($expressionParseSpecification, $referenceParseSpecification),
                            $expressionParseSpecification,
                            $referenceParseSpecification,
                            $hintParseSpecification,
                        ),
                        new TypeParseSpecification($hintParseSpecification),
                        fn(ParseSpecification $parentParseSpecification): ParseSpecification => new ExportParseSpecification($parentParseSpecification),
                        fn(ParseSpecification $parentParseSpecification): ParseSpecification => new DeclareParseSpecification($parentParseSpecification),
                        $expressionParseSpecification,
                    ]
                ),
            ],
        );
    }

    private function getCodeLexer(): CodeLexer
    {
        return $this->codeLexer ??= new TypescriptCodeLexer();
    }
}
