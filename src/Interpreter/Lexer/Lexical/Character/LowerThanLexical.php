<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character;

use Override;

/**
 * @psalm-immutable
 */
final class LowerThanLexical extends AbstractCharacterLexical
{
    public const TYPE = 'lowerThan';

    #[Override]
    protected function character(): string
    {
        return '<';
    }

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
