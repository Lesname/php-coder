<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character;

use Override;

/**
 * @psalm-immutable
 */
final class CommaLexical extends AbstractCharacterLexical
{
    public const TYPE = 'comma';
    public const CHARACTER = ',';

    #[Override]
    protected function character(): string
    {
        return ',';
    }

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
