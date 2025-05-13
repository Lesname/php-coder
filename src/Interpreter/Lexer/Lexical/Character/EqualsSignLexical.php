<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character;

use Override;

/**
 * @psalm-immutable
 */
final class EqualsSignLexical extends AbstractCharacterLexical
{
    public const TYPE = 'equalsSign';
    public const CHARACTER = '=';

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
