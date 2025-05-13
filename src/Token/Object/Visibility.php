<?php
declare(strict_types=1);

namespace LesCoder\Token\Object;

enum Visibility
{
    case Public;
    case Protected;
    case Private;
}
