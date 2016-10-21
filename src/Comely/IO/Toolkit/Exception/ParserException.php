<?php
declare(strict_types=1);

namespace Comely\IO\Toolkit\Exception;

use Comely\IO\Toolkit\ToolkitException;

/**
 * Class ParserException
 * @package Comely\IO\Toolkit\Exception
 */
class ParserException extends ToolkitException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Toolkit\\Parser";

    /**
     * @return ParserException
     */
    public static function badData() : self
    {
        return new self(
            self::$componentId,
            '$data param must be supplied with an associative Array or an Object',
            1101
        );
    }
}