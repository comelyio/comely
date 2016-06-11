<?php
declare(strict_types=1);

namespace Comely\IO\Database\Exception;

use Comely\IO\Database\DatabaseException;

/**
 * Class FluentException
 * @package Comely\IO\Database\Exception
 */
class FluentException extends DatabaseException
{
    protected static $componentId   =   "Comely\\IO\\Database\\Fluent";

    /**
     * @return FluentException
     */
    public static function badIntegerSize() : FluentException
    {
        return new self(
            self::$componentId,
            "Integer column size must be defined with one of Fluent::INT_* flags",
            1101
        );
    }

    /**
     * @return FluentException
     */
    public static function badStringFlag() : FluentException
    {
        return new self(
            self::$componentId,
            "String column size must be declared with one of the Fluent::STR_* flags",
            1102
        );
    }

    /**
     * @param string $badType
     * @param string $colType
     * @return FluentException
     */
    public static function badDefaultValue(string $badType, string $colType) : FluentException
    {
        return new self(
            self::$componentId,
            sprintf(
                'Default value data type "%1$s" must match with column\'s data type "%2$s"',
                strtoupper($badType),
                strtoupper($colType)
            ),
            1103
        );
    }
}