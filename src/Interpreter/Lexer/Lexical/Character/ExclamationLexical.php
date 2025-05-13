<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character;

use Override;

/**
 * @psalm-immutable
 */
final class ExclamationLexical extends AbstractCharacterLexical
{
    public const TYPE = 'exclamation';
    public const CHARACTER = '!';

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
