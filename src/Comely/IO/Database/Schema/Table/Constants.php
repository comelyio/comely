<?php
declare(strict_types=1);

namespace Comely\IO\Database\Schema\Table;

/**
 * Interface Constants
 * @package Comely\IO\Database\Schema\Table
 */
interface Constants
{
    const INT_TINY  =   1;
    const INT_SMALL =   2;
    const INT_MEDIUM    =   4;
    const INT_DEFAULT   =   8;
    const INT_BIG   =   16;
    const STR_FIXED =   32;
    const STR_VARIABLE  =   64;
}
