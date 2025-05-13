<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\Character\AbstractCharacterLexical;

/**
 * @psalm-immutable
 */
final class SquareBracketRightLexical extends AbstractCharacterLexical
{
    public const TYPE = 'squareBracketRight';
    public const CHARACTER = ']';

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
