<?php
declare(strict_types=1);

namespace LesCoder\Token\Value;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\NoImportsHelper;

/**
 * @psalm-immutable
 */
enum BuiltInCodeToken implements CodeToken
{
    use NoImportsHelper;

    case False;
    case True;
    case Null;
    case Parent;
}
