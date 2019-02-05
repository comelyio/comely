<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Cache\Exception;

use Comely\IO\Cache\Engine\Redis;
use Throwable;

/**
 * Class RedisException
 * @package Comely\IO\Cache\Exception
 */
class RedisException extends EngineException
{
    /**
     * RedisException constructor.
     * @param string $message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(Redis::ENGINE, $message, $code, $previous);
    }
}