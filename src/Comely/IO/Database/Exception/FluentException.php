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
     * @param string $constant
     * @param string $model
     * @return FluentException
     */
    public static function initConstant(string $constant, string $model)
    {
        return new self(
            self::$componentId,
            sprintf(
                'Fluent model "%1$s" must define "%2$s" constant',
                $model,
                $constant
            ),
            1101
        );
    }

    /**
     * @param string $model
     * @param string $expected
     * @param string $given
     * @return FluentException
     */
    public static function tableModelMismatch(string $model, string $expected, string $given) : FluentException
    {
        return new self(
            $model,
            sprintf(
                'Fluent model "%1$s" is related to table "%2$s" not "%3$s"',
                $model,
                $expected,
                $given
            ),
            1102
        );
    }

    /**
     * @param string $key
     * @param string $model
     * @return FluentException
     */
    public static function missingColumn(string $key, string $model) : FluentException
    {
        return new self($model, sprintf('Missing column "%1$s" in input row', $key), 1103);
    }

    /**
     * @param string $model
     * @param string $column
     * @param string $expected
     * @param string $given
     * @return FluentException
     */
    public static function badColumnValue(string $model, string $column, string $expected, string $given) : FluentException
    {
        return new self(
            $model,
            sprintf(
                'Column "%1$s" expects value type "%2$s" but given type is "%3$s"',
                $column,
                $expected,
                $given
            ),
            1104
        );
    }

    /**
     * @param string $method
     * @param string $error
     * @return FluentException
     */
    public static function arQueryError(string $method, string $error) : FluentException
    {
        return new self($method, $error, 1105);
    }
}