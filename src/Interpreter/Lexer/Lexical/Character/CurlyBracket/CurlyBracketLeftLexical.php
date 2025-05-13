<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\Character\AbstractCharacterLexical;

/**
 * @psalm-immutable
 */
final class CurlyBracketLeftLexical extends AbstractCharacterLexical
{
    public const TYPE = 'curlyBracketLeft';
    public const CHARACTER = '{';

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
