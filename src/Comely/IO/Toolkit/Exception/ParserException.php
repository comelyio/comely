<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

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