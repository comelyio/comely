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

namespace Comely\IO\Database;

/**
 * Class Config
 * @package Comely\IO\Database
 */
class Config
{
    /** @var bool */
    public $persistent;
    /** @var bool */
    public $silentMode;
    /** @var int */
    public $fetchCount;
    /** @var string|null */
    public $driver;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->fetchCount   =   Database::FETCH_COUNT_DEFAULT;
        $this->persistent   =   true;
        $this->silentMode   =   false;
    }
}