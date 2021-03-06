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

namespace Comely\IO\Database\Adapter;

/**
 * Class ServerCredentials
 * @package Comely\IO\Database\Adapter
 */
class ServerCredentials
{
    /** @var int */
    public $driver;
    /** @var string */
    public $driverName;
    /** @var string */
    public $dsn;
    /** @var string */
    public $database;
    /** @var null|string */
    public $username;
    /** @var null|string */
    public $password;
    /** @var bool */
    public $persistent;
}