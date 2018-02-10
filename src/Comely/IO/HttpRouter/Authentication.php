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

namespace Comely\IO\HttpRouter\Router;

use Comely\IO\HttpRouter\Authentication\BasicAuth;
use Comely\IO\HttpRouter\Authentication\User;

/**
 * Class Authentication
 * @package Comely\IO\HttpRouter\Router
 */
abstract class Authentication
{
    /** @var string */
    protected $realm;
    /** @var array */
    protected $users;
    /** @var null|callable */
    protected $unauthorized;

    /**
     * Authentication constructor.
     * @param string $realm
     */
    public function __construct(string $realm)
    {
        $this->realm = $realm;
        $this->users = [];
    }

    /**
     * @return void
     */
    abstract public function authenticate(): void;

    /**
     * @param string $realm
     * @return BasicAuth
     */
    final public static function Basic(string $realm): BasicAuth
    {
        return new BasicAuth($realm);
    }

    /**
     * @param string $username
     * @param string $password
     * @return Authentication
     */
    final public function user(string $username, string $password): self
    {
        $this->users[$username] = new User($username, $password);
        return $this;
    }

    /**
     * @param callable $callback
     * @return Authentication
     */
    final public function unauthorized(callable $callback): self
    {
        $this->unauthorized = $callback;
        return $this;
    }
}