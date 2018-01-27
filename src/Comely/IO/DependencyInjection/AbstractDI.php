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

namespace Comely\IO\DependencyInjection;

/**
 * Class AbstractDI
 * @package Comely\IO\DependencyInjection
 */
abstract class AbstractDI
{
    /**
     * @throws DependencyInjectionException
     */
    final public function __wakeup()
    {
        throw DependencyInjectionException::serializeContainer();
    }

    /**
     * @throws DependencyInjectionException
     */
    final public function __clone()
    {
        throw DependencyInjectionException::cloneContainer();
    }
}