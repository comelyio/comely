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

namespace Comely\IO\HttpRouter\Authentication;

/**
 * Class User
 * @package Comely\IO\HttpRouter\Authentication
 * @property string $username
 * @property string $password
 */
class User
{
    /** @var string */
    private $username;
    /** @var string */
    private $password;

    /**
     * User constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param $prop
     * @return null|string
     */
    public function __get($prop): ?string
    {
        return $this->$prop ?? null;
    }

    /**
     * @param $prop
     * @param $value
     * @return bool
     */
    public function __set($prop, $value): bool
    {
        return false;
    }
}