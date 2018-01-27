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
 * Class GlobalContainer
 * Singleton container for global access
 *
 * @package Comely\IO\DependencyInjection
 */
class GlobalContainer
{
    /** @var Container */
    private static $instance;

    /**
     * @return Container
     */
    public static function getInstance() : Container
    {
        if(!isset(self::$instance)) {
            self::$instance =   new Container();
        }

        return self::$instance;
    }

    /**
     * GlobalContainer constructor.
     */
    private function __construct()
    {
    }
}