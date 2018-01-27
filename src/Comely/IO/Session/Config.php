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

namespace Comely\IO\Session;

use Comely\IO\Security\Cipher;

/**
 * Class Config
 * @package Comely\IO\Session
 */
class Config
{
    /** @var string */
    public $hashSalt;
    /** @var int */
    public $hashCost;
    /** @var null|Cipher */
    public $cipher;
    /** @var bool */
    public $cookie;
    /** @var int */
    public $cookieLife;
    /** @var string */
    public $cookiePath;
    /** @var string */
    public $cookieDomain;
    /** @var bool */
    public $cookieSecure;
    /** @var bool */
    public $cookieHttpOnly;
    /** @var int */
    public $sessionLife;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->hashSalt =   "";
        $this->hashCost =   1;
        $this->cipher   =   null;
        $this->cookie   =   true;
        $this->cookieLife   =   2592000;
        $this->cookiePath   =   "";
        $this->cookieDomain =   "";
        $this->cookieHttpOnly   =   true;
        $this->cookieSecure =   true;
        $this->sessionLife   =   3600;
    }
}