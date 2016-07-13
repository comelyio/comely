<?php
declare(strict_types=1);

namespace Comely\IO\Session;

use Comely\IO\Security\Cipher;

/**
 * Class ConfigTrait
 * @package Comely\IO\Session
 */
trait ConfigTrait
{
    /**
     * Sets salt for PBKDF2 payload hashin
     *
     * @param string $salt
     * @return Session
     */
    public function setHashSalt(string $salt) : Session
    {
        $this->config->hashSalt =   $salt;
        return $this;
    }

    /**
     * Sets cost for PBKDF2 payload hashing
     *
     * @param int $cost
     * @return Session
     * @throws SessionException
     */
    public function setHashCost(int $cost) : Session
    {
        if($cost    <=   0) {
            throw SessionException::configError("hashCost", "Cost must be a positive integer");
        }

        $this->config->hashCost =   $cost;
        return $this;
    }

    /**
     * Encrypt session payload with Cipher component
     *
     * @param Cipher $cipher
     * @return Session
     */
    public function useCipher(Cipher $cipher) : Session
    {
        $this->config->cipher   =   $cipher;
        return $this;
    }

    /**
     * Set preference for COMELYSESSID cookie
     *
     * @param bool $set
     * @param int $life
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @throws SessionException
     */
    public function setCookie(
        bool $set = false,
        int $life = 0,
        string $path = "",
        string $domain = "",
        bool $secure = true,
        bool $httpOnly = true
    ) {
        if($life    <=   0) {
            throw SessionException::configError("cookieLife", "Expiry in seconds must be a positive integer");
        }

        $this->config->cookie   =   $set;
        $this->config->cookieLife   =   $life;
        $this->config->cookiePath   =   $path;
        $this->config->cookieDomain   =   $domain;
        $this->config->cookieSecure   =   $secure;
        $this->config->cookieHttpOnly   =   $httpOnly;
    }

    /**
     * Session will expire after given number of seconds of inactivity
     *
     * @param int $secs
     * @return Session
     * @throws SessionException
     */
    public function setSessionLife(int $secs) : Session
    {
        if($secs    <=   0) {
            throw SessionException::configError("sessionLife", "Expiry in seconds must be a positive integer");
        }

        $this->config->sessionLife  =   $secs;
        return $this;
    }
}