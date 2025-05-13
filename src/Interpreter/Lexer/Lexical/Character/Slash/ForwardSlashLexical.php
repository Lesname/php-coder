<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical\Character\Slash;

use Override;
use LesCoder\Interpreter\Lexer\Lexical\Character\AbstractCharacterLexical;

/**
 * @psalm-immutable
 */
final class ForwardSlashLexical extends AbstractCharacterLexical
{
    public const TYPE = 'forwardSlash';
    public const CHARACTER = '/';

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
