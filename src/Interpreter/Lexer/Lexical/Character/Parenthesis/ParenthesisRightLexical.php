<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\Character\AbstractCharacterLexical;

/**
 * @psalm-immutable
 */
final class ParenthesisRightLexical extends AbstractCharacterLexical
{
    public const TYPE = 'parenthesisRight';
    public const CHARACTER = ')';

    #[Override]
    protected function character(): string
    {
        return self::CHARACTER;
    }

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
