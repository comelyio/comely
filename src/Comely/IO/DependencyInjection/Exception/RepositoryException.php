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

namespace Comely\IO\DependencyInjection\Exception;

use Comely\IO\DependencyInjection\DependencyInjectionException;

/**
 * Class RepositoryException
 * @package Comely\IO\DependencyInjection\Exception
 */
class RepositoryException extends DependencyInjectionException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\DependencyInjection\\Repository";

    /**
     * @return RepositoryException
     */
    public static function badInstance() : self
    {
        return new self(self::$componentId, "Only instances can be stored", 1101);
    }

    /**
     * @param string $key
     * @return RepositoryException
     */
    public static function instanceNotFound(string $key) : self
    {
        return new self(
            self::$componentId,
            sprintf('Instance not found with key "%1$s"', $key),
            1102
        );
    }
}