<?php
declare(strict_types=1);

namespace Comely\IO\Session;

/**
 * Class Config
 * @package Comely\IO\Session
 */
class Config
{
    public $hashSalt;
    public $hashCost;
    public $cipher;
    public $cookie;
    public $cookieLife;
    public $cookiePath;
    public $cookieDomain;
    public $cookieSecure;
    public $cookieHttpOnly;
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
        $this->cookiePath   =   null;
        $this->cookieDomain =   null;
        $this->cookieHttpOnly   =   true;
        $this->cookieSecure =   true;
        $this->sessionLife   =   3600;
    }
}