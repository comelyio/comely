<?php
declare(strict_types=1);

namespace Comely\IO\Database\Exception;

use Comely\IO\Database\DatabaseException;

/**
 * Class SchemaException
 * @package Comely\IO\Database\Exception
 */
class SchemaException extends DatabaseException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Database\\Schema";

    /**
     * @return SchemaException
     */
    public static function badIntegerSize() : self
    {
        return new self(
            self::$componentId,
            "Integer column size must be defined with one of AbstractTable::INT_* flags",
            1201
        );
    }

    /**
     * @param string $column
     * @param string $flagSet
     * @return SchemaException
     */
    public static function badFlag(string $column, string $flagSet) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Column "%1$s" must be defined with one of self::%2$s_* flags',
                $column,
                strtoupper($flagSet)
            ),
            1202
        );
    }

    /**
     * @param string $badType
     * @param string $colType
     * @return SchemaException
     */
    public static function badDefaultValue(string $badType, string $colType) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Default value data type "%1$s" must match with column\'s data type "%2$s"',
                strtoupper($badType),
                strtoupper($colType)
            ),
            1203
        );
    }

    /**
     * @param string $colName
     * @param string $colType
     * @param string $dbDriver
     * @return SchemaException
     */
    public static function unSupportedColumn(string $colName, string $colType, string $dbDriver) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Database driver "%1$s" does not support column type "%2$s" for column "%3$s"',
                strtoupper($dbDriver),
                strtoupper($colType),
                $colName
            ),
            1204
        );
    }

    /**
     * @param string $error
     * @return SchemaException
     */
    public static function columnParseError(string $error) : self
    {
        return new self(self::$componentId, $error, 1205);
    }

    /**
     * @param string $method
     * @param int $index
     * @param string $expected
     * @param string $given
     * @return SchemaException
     */
    public static function badArgType(string $method, int $index, string $expected, string $given) : self
    {
        return new self(
            $method,
            sprintf(
                'Argument %1$d expects "%2$s" but given type is "%3$s"',
                $index,
                $expected,
                $given
            ),
            1206
        );
    }

    /**
     * @param string $table
     * @return SchemaException
     */
    public static function tableNotFound(string $table) : self
    {
        return new self(self::$componentId, sprintf('Table "%1$s" not found', $table), 1207);
    }

    /**
     * @param string $const
     * @return SchemaException
     */
    public static function tableInitConstant(string $const) : self
    {
        return new self(self::$componentId, sprintf('Table must defined "%1$s" constant', $const), 1208);
    }

    /**
     * @param string $table
     * @param string $model
     * @return SchemaException
     */
    public static function badModel(string $table, string $model) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Model/data-mapping class "%1$s" for table "%2$s" not found',
                $model,
                $table
            ),
            1209
        );
    }

    /**
     * @param string $name
     * @return SchemaException
     */
    public static function badColumnName(string $name) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Column name "%1$s" is incompatible. Column names must use "snake_case" naming convention',
                $name
            ),
            1210
        );
    }

    /**
     * @param string $method
     * @return SchemaException
     */
    public static function undefinedMethod(string $method) : self
    {
        return new self(self::$componentId, sprintf('Calling undefined method "%1$s"', $method), 1211);
    }

    /**
     * @param string $column
     * @param string $table
     * @return SchemaException
     */
    public static function undefinedColumn(string $column, string $table) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Column "%1$s" in not defined in table "%2$s"',
                $column,
                $table
            ),
            1212
        );
    }
}