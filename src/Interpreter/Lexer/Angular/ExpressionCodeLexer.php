<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Angular;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\CodeLexer;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Interpreter\Lexer\SpecificationCodeLexer;
use LesCoder\Interpreter\Lexer\Lexical\Character\DotLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PlusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\TildeLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\MinusLexical;
use LesCoder\Interpreter\Lexer\Specification\PipeSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\AtSignLexical;
use LesCoder\Interpreter\Lexer\Specification\LabelSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\AsteriskLexical;
use LesCoder\Interpreter\Lexer\Specification\StringSpecification;
use LesCoder\Interpreter\Lexer\Specification\IntegerSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PercentageLexical;
use LesCoder\Interpreter\Lexer\Specification\AmpersandSpecification;
use LesCoder\Interpreter\Lexer\Specification\CharacterSpecification;
use LesCoder\Interpreter\Lexer\Specification\ComparisonSpecification;
use LesCoder\Interpreter\Lexer\Specification\WhitespaceSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Specification\Typescript\QuestionMarkSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

final class ExpressionCodeLexer implements CodeLexer
{
    private ?CodeLexer $proxy = null;

    #[Override]
    public function tokenize(StringStream $stream): LexicalStream
    {
        return $this->proxy()->tokenize($stream);
    }

    private function proxy(): CodeLexer
    {
        return $this->proxy ??= new SpecificationCodeLexer(
            [
                new AmpersandSpecification(),
                new ComparisonSpecification(),
                new PipeSpecification(),
                new QuestionMarkSpecification(),

                new StringSpecification(["'", '"']),
                new IntegerSpecification(),

                new LabelSpecification(),

                new WhitespaceSpecification(),

                new CharacterSpecification(ForwardSlashLexical::CHARACTER, ForwardSlashLexical::class),
                new CharacterSpecification(AsteriskLexical::CHARACTER, AsteriskLexical::class),
                new CharacterSpecification(ColonLexical::CHARACTER, ColonLexical::class),
                new CharacterSpecification(CommaLexical::CHARACTER, CommaLexical::class),
                new CharacterSpecification(DotLexical::CHARACTER, DotLexical::class),
                new CharacterSpecification(PercentageLexical::CHARACTER, PercentageLexical::class),
                new CharacterSpecification(SemicolonLexical::CHARACTER, SemicolonLexical::class),
                new CharacterSpecification(TildeLexical::CHARACTER, TildeLexical::class),
                new CharacterSpecification(AtSignLexical::CHARACTER, AtSignLexical::class),
                new CharacterSpecification(MinusLexical::CHARACTER, MinusLexical::class),
                new CharacterSpecification(PlusLexical::CHARACTER, PlusLexical::class),

                new CharacterSpecification(CurlyBracketLeftLexical::CHARACTER, CurlyBracketLeftLexical::class),
                new CharacterSpecification(CurlyBracketRightLexical::CHARACTER, CurlyBracketRightLexical::class),
                new CharacterSpecification(ParenthesisLeftLexical::CHARACTER, ParenthesisLeftLexical::class),
                new CharacterSpecification(ParenthesisRightLexical::CHARACTER, ParenthesisRightLexical::class),
                new CharacterSpecification(SquareBracketLeftLexical::CHARACTER, SquareBracketLeftLexical::class),
                new CharacterSpecification(SquareBracketRightLexical::CHARACTER, SquareBracketRightLexical::class),
            ],
        );
    }
}
