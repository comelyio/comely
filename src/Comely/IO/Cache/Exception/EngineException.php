<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Cache\Exception;

use Throwable;

/**
 * Class EngineException
 * @package Comely\IO\Cache\Exception
 */
class EngineException extends CacheException
{
    /**
     * EngineException constructor.
     * @param string $engine
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $engine, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('[%1$s] %2$s', $engine, $message), $code, $previous);
    }

    /**
     * @param string $engine
     * @param string $key
     * @return EngineException
     */
    public static function errorStore(string $engine, string $key): self
    {
        return new self($engine, sprintf('Failed to store key "%1$s"', $key));
    }

    /**
     * @param string $engine
     * @param string $key
     * @return EngineException
     */
    public static function errorFetch(string $engine, string $key): self
    {
        return new self($engine, sprintf('Failed to fetch stored item with key "%1$s"', $key));
    }

    /**
     * @param string $engine
     * @param string $key
     * @return EngineException
     */
    public static function errorHas(string $engine, string $key): self
    {
        return new self($engine, sprintf('Failed to check existence of key "%1$s"', $key));
    }

    /**
     * @param string $engine
     * @param string $key
     * @return EngineException
     */
    public static function errorDelete(string $engine, string $key): self
    {
        return new self($engine, sprintf('Failed to delete cached key "%1$s"', $key));
    }

    /**
     * @param string $engine
     * @return EngineException
     */
    public static function errorFlush(string $engine): self
    {
        return new self($engine, 'Failed to flush all cached keys/values');
    }

    /**
     * @param string $engine
     * @param string $key
     * @param string $action
     * @return EngineException
     */
    public static function errorCount(string $engine, string $key, string $action): self
    {
        return new self($engine, sprintf('Failed to %2$s key "%1$s"', $key, $action));
    }
}