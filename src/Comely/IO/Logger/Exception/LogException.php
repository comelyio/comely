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

namespace Comely\IO\Logger\Exception;

use Comely\IO\Logger\LoggerException;

/**
 * Class LogException
 * @package Comely\IO\Logger\Exception
 */
class LogException extends LoggerException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Logger\\Log";
}