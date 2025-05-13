<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\Character\AbstractCharacterLexical;

/**
 * @psalm-immutable
 */
final class CurlyBracketRightLexical extends AbstractCharacterLexical
{
    public const TYPE = 'curlyBracketRight';
    public const CHARACTER = '}';

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
