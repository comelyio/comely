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

namespace Comely\IO\Session;

/**
 * Class Cookie
 * @package Comely\IO\Session
 */
class Cookie
{
    /** @var int */
    private $expire;
    /** @var string */
    private $path;
    /** @var string */
    private $domain;
    /** @var bool */
    private $secure;
    /** @var bool */
    private $httpOnly;

    /**
     * Cookie constructor.
     */
    public function __construct()
    {
        $this->expire = 604800; // 1 week
        $this->path = "/";
        $this->domain = "";
        $this->secure = true;
        $this->httpOnly = true;
    }

    /**
     * @param int $seconds
     * @return Cookie
     */
    public function expire(int $seconds): self
    {
        $this->expire = $seconds;
        return $this;
    }

    /**
     * @param string $path
     * @return Cookie
     */
    public function path(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $domain
     * @return Cookie
     */
    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param bool $https
     * @return Cookie
     */
    public function secure(bool $https): self
    {
        $this->secure = $https;
        return $this;
    }

    /**
     * @param bool $httpOnly
     * @return Cookie
     */
    public function httpOnly(bool $httpOnly): self
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function set(string $name, string $value): bool
    {
        return setcookie(
            $name,
            $value,
            time() + $this->expire,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
    }
}