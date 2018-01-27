<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Session\Exception;

use Comely\IO\Session\SessionException;

/**
 * Class ComelySessionException
 * @package Comely\IO\Session\Exception
 */
class ComelySessionException extends SessionException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Session\\ComelySession";

    /**
     * @param string $method
     * @param string $type
     * @return ComelySessionException
     */
    public static function badPropValue(string $method, string $type) : self
    {
        return new self($method, sprintf('Value type "%1$s" cannot be stored', $type), 1101);
    }
}